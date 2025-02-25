<template>
  <div class="availability-container">
    <div class="container py-4">
      <!-- Header Section -->
      <div class="availability-header">
        <h2><i class="bi bi-grid-3x3-gap me-2"></i>Workspace Availability</h2>
        <p class="text-muted">Find and book available workspaces for your needs</p>
      </div>
      
      <!-- Date Selection -->
      <div class="date-selection-card mb-4">
        <div class="row align-items-center">
          <div class="col-md-6">
            <label for="date-picker" class="form-label">Select Date</label>
            <div class="input-group date-picker-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-calendar3"></i>
              </span>
              <input 
                type="date" 
                id="date-picker" 
                class="form-control border-start-0" 
                v-model="selectedDate"
              >
              <button @click="fetchAvailability" class="btn search-btn ms-2">
                <i class="bi bi-search me-2"></i>Search
              </button>
            </div>
          </div>
          <div class="col-md-6 mt-3 mt-md-0">
            <div class="legend-container">
              <div class="legend-item">
                <div class="legend-color available"></div>
                <span>Available</span>
              </div>
              <div class="legend-item">
                <div class="legend-color occupied"></div>
                <span>Occupied</span>
              </div>
              <div class="legend-item">
                <div class="legend-color reserved"></div>
                <span>Reserved, unpaid</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center my-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading workspace availability...</p>
      </div>
      
      <!-- Error State -->
      <div v-if="error" class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ error }}
      </div>
      
      <!-- Availability Table -->
      <div v-if="!loading && !error && spaces.length > 0" class="availability-table-container">
        <div class="table-responsive">
          <table class="availability-table">
            <thead>
              <tr>
                <th class="workspace-header">Workspace</th>
                <th v-for="hour in hours" :key="hour">{{ hour }}h</th>
                <th class="actions-header">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="space in spaces" :key="space.id">
                <td class="workspace-name">
                  <div class="ws-badge">{{ space.label.charAt(0).toUpperCase() }}</div>
                  <span>{{ capitalizeFirstLetter(space.label) }}</span>
                </td>
                <td 
                  v-for="hour in hours" 
                  :key="`${space.id}-${hour}`"
                  :class="getCellClass(getSlotStatus(space, hour))"
                >
                  <div class="slot-tooltip">{{ getSlotStatus(space, hour) }}</div>
                </td>
                <td class="action-cell">
                  <button class="btn reserve-btn" @click="handleReserve(space)">
                    Reserve
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- No Data State -->
      <div v-if="!loading && !error && spaces.length === 0" class="no-data-container">
        <i class="bi bi-calendar-x display-4 text-muted"></i>
        <h4>No workspaces available</h4>
        <p>Try selecting a different date or contact support for assistance.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'WorkspaceAvailability',
  data() {
    return {
      selectedDate: this.formatDate(new Date()),
      spaces: [],
      hours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
      loading: false,
      error: null,
      apiClient: null
    }
  },
  created() {
    this.apiClient = axios.create({
      baseURL: 'http://localhost:8000/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    // Set auth token if exists
    const token = localStorage.getItem('token');
    if (token) {
      this.apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
    
    this.fetchAvailability();
  },
  methods: {
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    async fetchAvailability() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await this.apiClient.get(`/front-office/espaces/availability?date=${this.selectedDate}`);
        this.spaces = response.data.espaces;
      } catch (error) {
        this.error = 'Failed to load workspace availability. Please try again later.';
        console.error('Error fetching workspace availability:', error);
      } finally {
        this.loading = false;
      }
    },
    
    getSlotStatus(space, hour) {
      const slot = space.slots.find(s => s.hour === hour);
      return slot ? slot.status : 'unavailable';
    },
    
    getCellClass(status) {
      if (status === 'libre') return 'available-cell';
      if (status === 'occupé') return 'occupied-cell';
      if (status === 'Reservé, non payé') return 'reserved-cell';
      return '';
    },
    
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    
    handleReserve(space) {
      alert(`Reservation for workspace ${space.label} will be implemented in the next phase.`);
    }
  }
}
</script>

<style scoped>
.availability-container {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  min-height: calc(100vh - 72px); /* Adjust based on your navbar height */
  font-family: 'Poppins', sans-serif;
}

.availability-header {
  margin-bottom: 1.5rem;
}

.availability-header h2 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0.3rem;
}

.date-selection-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  transition: transform 0.2s ease;
}

.form-label {
  font-weight: 500;
  color: #495057;
}

.date-picker-group .form-control {
  border-radius: 8px;
  padding: 0.6rem 1rem;
}

.search-btn {
  background: linear-gradient(to right, #4e73df, #224abe);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.6rem 1.2rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.search-btn:hover {
  background: linear-gradient(to right, #224abe, #1a3997);
  box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
}

/* Legend Styles */
.legend-container {
  display: flex;
  justify-content: flex-end;
  gap: 1.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-color {
  width: 20px;
  height: 20px;
  border-radius: 4px;
}

.legend-color.available {
  background-color: #d1e7dd;
  border: 1px solid #badbcc;
}

.legend-color.occupied {
  background-color: #f8d7da;
  border: 1px solid #f5c2c7;
}

.legend-color.reserved {
  background-color: #e2d9f3;
  border: 1px solid #d0c5e8;
}

/* Table Styles */
.availability-table-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  margin-top: 1.5rem;
  overflow: hidden;
}

.availability-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 4px;
}

.availability-table th {
  padding: 1rem 0.75rem;
  text-align: center;
  background-color: #f1f5ff;
  color: #4e73df;
  font-weight: 600;
  border-radius: 8px;
}

.availability-table .workspace-header {
  text-align: left;
  min-width: 150px;
}

.availability-table .actions-header {
  width: 120px;
}

.availability-table td {
  padding: 1rem 0.75rem;
  text-align: center;
  border-radius: 8px;
  position: relative;
}

.workspace-name {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-align: left;
  font-weight: 500;
  color: #2b3a4a;
}

.ws-badge {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(45deg, #4e73df, #224abe);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.available-cell {
  background-color: #d1e7dd;
  border: 1px solid #badbcc;
  cursor: pointer;
}

.occupied-cell {
  background-color: #f8d7da;
  border: 1px solid #f5c2c7;
}

.reserved-cell {
  background-color: #e2d9f3;
  border: 1px solid #d0c5e8;
}

.slot-tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

td:hover .slot-tooltip {
  opacity: 1;
  visibility: visible;
}

.action-cell {
  vertical-align: middle;
}

.reserve-btn {
  background-color: #f1f5ff;
  color: #4e73df;
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.8rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.reserve-btn:hover {
  background-color: #4e73df;
  color: white;
}

/* No Data State */
.no-data-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  margin-top: 1.5rem;
  text-align: center;
}

.no-data-container i {
  margin-bottom: 1rem;
  color: #6c757d;
}

.no-data-container h4 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0.5rem;
}

.no-data-container p {
  color: #6c757d;
  max-width: 400px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .legend-container {
    justify-content: flex-start;
    margin-top: 1rem;
  }
}

@media (max-width: 768px) {
  .legend-container {
    flex-wrap: wrap;
    gap: 1rem;
  }
}
</style>