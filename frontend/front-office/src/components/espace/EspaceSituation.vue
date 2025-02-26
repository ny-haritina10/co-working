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
                    Reservation
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

    <!-- Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reservationModalLabel">Reserve {{ selectedSpace ? capitalizeFirstLetter(selectedSpace.label) : '' }} Workspace</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitReservation">
              <!-- Date Field (read-only) -->
              <div class="mb-3">
                <label for="reservation-date" class="form-label">Date</label>
                <input 
                  type="date" 
                  id="reservation-date" 
                  class="form-control" 
                  v-model="reservationForm.date" 
                  readonly
                >
              </div>
              
              <!-- Hour Selection -->
              <div class="mb-3">
                <label for="start-hour" class="form-label">Start Time</label>
                <select id="start-hour" class="form-select" v-model="reservationForm.hourBegin" required>
                  <option value="" disabled>Select start time</option>
                  <option v-for="hour in availableStartHours" :key="hour" :value="hour">{{ hour }}:00</option>
                </select>
              </div>
              
              <!-- Duration Selection -->
              <div class="mb-3">
                <label for="duration" class="form-label">Duration (hours)</label>
                <select id="duration" class="form-select" v-model.number="reservationForm.duration" required>
                  <option value="" disabled>Select duration</option>
                  <option v-for="duration in [1, 2, 3, 4]" :key="duration" :value="duration">
                    {{ duration }} hour{{ duration > 1 ? 's' : '' }}
                  </option>
                </select>
              </div>
              
              <!-- Options Checkboxes -->
              <div class="mb-3">
                <label class="form-label">Additional Options</label>
                <div v-if="optionsLoading" class="text-center my-2">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading options...</span>
                  </div>
                </div>
                <div v-else-if="optionsError" class="text-danger small">
                  {{ optionsError }}
                </div>
                <div v-else class="options-container">
                  <div v-for="option in options" :key="option.id" class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :id="`option-${option.id}`" 
                      :value="option.id" 
                      v-model="reservationForm.options"
                    >
                    <label class="form-check-label d-flex justify-content-between" :for="`option-${option.id}`">
                      {{ option.label }}
                      <span class="text-muted">${{ parseFloat(option.price).toLocaleString() }}</span>
                    </label>
                  </div>
                </div>
                <div v-if="!optionsLoading && !optionsError && options.length === 0" class="text-muted small">
                  No additional options available
                </div>
              </div>
              
              <!-- Price Summary -->
              <div class="price-summary mt-4">
                <h6 class="border-bottom pb-2">Reservation Summary</h6>
                <div class="d-flex justify-content-between">
                  <span>Workspace ({{ reservationForm.duration || 0 }} hour{{ reservationForm.duration !== 1 ? 's' : '' }})</span>
                  <span>${{ calculateWorkspacePrice().toLocaleString() }}</span>
                </div>
                <div v-for="option in selectedOptions" :key="option.id" class="d-flex justify-content-between">
                  <span>{{ option.label }}</span>
                  <span>${{ parseFloat(option.price).toLocaleString() }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top">
                  <span>Total</span>
                  <span>${{ calculateTotalPrice().toLocaleString() }}</span>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
              type="button" 
              class="btn btn-primary" 
              @click="submitReservation"
              :disabled="reservationLoading"
            >
              <span v-if="reservationLoading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Confirm Reservation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="successModalLabel">Reservation Successful</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-3">
              <i class="bi bi-check-circle-fill text-success display-1"></i>
            </div>
            <h4 class="text-center">Your workspace has been reserved!</h4>
            <div class="reservation-details mt-4" v-if="reservationSuccess">
              <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span class="detail-value">{{ reservationSuccess.reference || 'N/A' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Workspace:</span>
                <span class="detail-value">{{ capitalizeFirstLetter(reservationSuccess.espace.label) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{{ formatDateTime(reservationSuccess.datetime_reservation) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span class="detail-value">{{ reservationSuccess.hour_duration }} hour(s)</span>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';

export default {
  name: 'EspaceSituation',
  data() {
    return {
      selectedDate: this.formatDate(new Date()),
      spaces: [],
      hours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
      loading: false,
      error: null,
      apiClient: null,
      client: null,
      
      // Modal related data
      reservationModal: null,
      successModal: null,
      selectedSpace: null,
      
      // Options data
      options: [],
      optionsLoading: false,
      optionsError: null,
      
      // Reservation form
      reservationForm: {
        date: '',
        hourBegin: '',
        duration: '',
        options: []
      },
      
      // Reservation process
      reservationLoading: false,
      reservationError: null,
      reservationSuccess: null
    }
  },
  computed: {
    availableStartHours() {
      // For simplicity, returning fixed hours 7-17
      // In a real application, you might want to filter based on availability
      return Array.from({ length: 11 }, (_, i) => i + 7);
    },
    selectedOptions() {
      return this.options.filter(option => this.reservationForm.options.includes(option.id));
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
  mounted() {
    // Initialize Bootstrap modals
    this.reservationModal = new Modal(document.getElementById('reservationModal'));
    this.successModal = new Modal(document.getElementById('successModal'));
  },
  methods: {
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    
    formatDateTime(dateTimeString) {
      if (!dateTimeString) return 'N/A';
      
      const dateTime = new Date(dateTimeString);
      const date = this.formatDate(dateTime);
      const hours = String(dateTime.getHours()).padStart(2, '0');
      const minutes = String(dateTime.getMinutes()).padStart(2, '0');
      
      return `${date} at ${hours}:${minutes}`;
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
    
    async fetchOptions() {
      this.optionsLoading = true;
      this.optionsError = null;
      
      try {
        const response = await this.apiClient.get('/back-office/options');
        if (response.data.success) {
          this.options = response.data.data;
        } else {
          this.optionsError = 'Failed to load options.';
        }
      } catch (error) {
        this.optionsError = 'Failed to load options. Please try again later.';
        console.error('Error fetching options:', error);
      } finally {
        this.optionsLoading = false;
      }
    },
    
    handleReserve(space) {
      this.selectedSpace = space;
      this.resetReservationForm();
      this.reservationForm.date = this.selectedDate;
      
      // Fetch options for the modal
      this.fetchOptions();
      
      // Open the modal
      this.reservationModal.show();
    },
    
    resetReservationForm() {
      this.reservationForm = {
        date: this.selectedDate,
        hourBegin: '',
        duration: '',
        options: []
      };
      this.reservationError = null;
    },
    
    calculateWorkspacePrice() {
      if (!this.selectedSpace || !this.reservationForm.duration) {
        return 0;
      }

      const hourPrice = parseFloat(this.selectedSpace.hour_price) || 0; 
      const duration = parseInt(this.reservationForm.duration, 10) || 0; 

      return hourPrice * duration;
    },

    calculateTotalPrice() {
      const workspacePrice = this.calculateWorkspacePrice();
      const optionsPrice = this.selectedOptions.reduce((total, option) => {
        return total + parseFloat(option.price);
      }, 0);
      
      return workspacePrice + optionsPrice;
    },
    
    async submitReservation() {
      // Validate form
      if (!this.reservationForm.hourBegin || !this.reservationForm.duration) {
        this.reservationError = 'Please fill all required fields.';
        return;
      }
      
      this.reservationLoading = true;
      this.reservationError = null;
      
      try {
        const clientData = localStorage.getItem('client')
        if (clientData) {
          this.client = JSON.parse(clientData)
        }        

        const userId = this.client.id; 

        const payload = {
          id_espace: this.selectedSpace.id,
          id_client: userId,
          date_reservation: this.reservationForm.date,
          hour_begin: this.reservationForm.hourBegin,
          duration: this.reservationForm.duration,
          options: this.reservationForm.options
        };
        
        // Send the reservation request
        const response = await this.apiClient.post('/front-office/reservations', payload);
        
        // Store the successful reservation data
        this.reservationSuccess = response.data.data;
        
        // Close the reservation modal
        this.reservationModal.hide();
        
        // Show the success modal
        this.successModal.show();
        
        // Refresh availability after successful reservation
        this.fetchAvailability();
        
      } catch (error) {
        this.reservationError = error.response?.data?.message || 'Failed to create reservation. Please try again.';
        console.error('Error creating reservation:', error);
      } finally {
        this.reservationLoading = false;
      }
    }
  }
}
</script>

<style scoped>
/* Reservation Modal Styles */
.modal-header {
  background-color: #f8f9fa;
}

.options-container {
  max-height: 200px;
  overflow-y: auto;
  padding-right: 10px;
}

.form-check {
  margin-bottom: 8px;
}

.price-summary {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 5px;
}

/* Success Modal Styles */
.reservation-details {
  margin-top: 20px;
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 5px;
}

.detail-row {
  display: flex;
  margin-bottom: 10px;
}

.detail-label {
  font-weight: 600;
  min-width: 120px;
}

.detail-value {
  flex: 1;
}

/* Button Styles */
.reserve-btn {
  background-color: #4a6fdc;
  color: white;
  border: none;
  padding: 8px 16px;
  transition: all 0.3s ease;
}

.reserve-btn:hover {
  background-color: #3a5bb9;
  transform: translateY(-2px);
}

.search-btn {
  background-color: #4a6fdc;
  color: white;
  border: none;
}

.search-btn:hover {
  background-color: #3a5bb9;
}

/* Table Styles */
.availability-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  overflow: hidden;
}

.availability-table th, 
.availability-table td {
  padding: 12px;
  border-bottom: 1px solid #e9ecef;
  text-align: center;
}

.availability-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.workspace-header, 
.actions-header {
  text-align: left;
}

.workspace-name {
  display: flex;
  align-items: center;
  text-align: left;
}

.ws-badge {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #4a6fdc;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
  font-weight: 600;
}

.available-cell {
  background-color: rgba(40, 167, 69, 0.1);
  position: relative;
}

.occupied-cell {
  background-color: rgba(220, 53, 69, 0.1);
  position: relative;
}

.reserved-cell {
  background-color: rgba(255, 193, 7, 0.1);
  position: relative;
}

.slot-tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  z-index: 10;
}

td:hover .slot-tooltip {
  opacity: 1;
  visibility: visible;
}

/* Legend Styles */
.legend-container {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
  font-size: 14px;
}

.legend-color {
  width: 15px;
  height: 15px;
  border-radius: 3px;
  margin-right: 5px;
}

.legend-color.available {
  background-color: rgba(40, 167, 69, 0.2);
  border: 1px solid rgba(40, 167, 69, 0.5);
}

.legend-color.occupied {
  background-color: rgba(220, 53, 69, 0.2);
  border: 1px solid rgba(220, 53, 69, 0.5);
}

.legend-color.reserved {
  background-color: rgba(255, 193, 7, 0.2);
  border: 1px solid rgba(255, 193, 7, 0.5);
}

/* Container Styles */
.availability-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
}

.availability-header {
  margin-bottom: 24px;
}

.date-selection-card {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.availability-table-container {
  margin-top: 20px;
}

.no-data-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #6c757d;
  background-color: #f8f9fa;
  border-radius: 8px;
  margin-top: 20px;
}

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