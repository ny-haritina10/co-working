<template>
  <div class="home-container">
    <!-- Navbar -->
    <Navbar :client="client" @logout="logout" />
    
    <!-- Main Content -->
    <div class="container main-content">
      <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
          <Sidebar :client="client" currentPage="reservation" />
        </div>
                
        <!-- Main Reservation Content -->
        <div class="col-lg-9">
          <div class="dashboard-header">
            <h2>My Bookings</h2>
            <p class="dashboard-date">{{ currentDate }}</p>
          </div>
          
          <!-- Reservations Card -->
          <div class="profile-card">
            <div class="profile-header">
              <h4><i class="bi bi-calendar-check me-2"></i>My Reservations</h4>
            </div>
            
            <div class="profile-body">
              <!-- Loading State -->
              <div v-if="loading" class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading your reservations...</p>
              </div>
              
              <!-- Error State -->
              <div v-else-if="error" class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ error }}
              </div>
              
              <!-- Empty State -->
              <div v-else-if="reservations.length === 0" class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                You don't have any reservations yet.
              </div>
              
              <!-- Reservations Table -->
              <div v-else class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Duration</th>
                      <th>Options</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(reservation, index) in reservations" :key="index">
                      <td>
                        <span class="fw-medium">{{ formatDate(reservation.reservation_date) }}</span>
                      </td>
                      <td>
                        <i class="bi bi-clock me-1"></i>
                        {{ reservation.hour_begin }} - {{ reservation.hour_end }}
                      </td>
                      <td>
                        {{ reservation.duration }} hour{{ reservation.duration > 1 ? 's' : '' }}
                      </td>
                      <td>
                        <div v-if="reservation.options.length > 0">
                          <span v-for="(option, optIndex) in reservation.options" :key="optIndex" 
                                class="badge bg-light text-dark me-1 mb-1">
                            <i class="bi bi-gear-fill me-1 small"></i>{{ option }}
                          </span>
                        </div>
                        <span v-else class="text-muted">No options</span>
                      </td>
                      <td>
                        <span class="fw-medium">{{ formatCurrency(reservation.reservation_amount) }}</span>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusBadgeClass(reservation.status)">
                          <i class="bi" :class="getStatusIconClass(reservation.status)"></i>
                          {{ reservation.status }}
                        </span>
                      </td>
                      <td>
                        <button 
                          v-if="showPayButton(reservation)"
                          @click="processPayment(reservation)" 
                          :disabled="isProcessingPayment(reservation.id_reservation)"
                          class="btn btn-sm btn-success">
                          <span v-if="isProcessingPayment(reservation.id_reservation)" class="spinner-border spinner-border-sm me-1" role="status"></span>
                          <i v-else class="bi bi-credit-card me-1"></i>
                          Payé
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Payment Success Modal -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="paymentSuccessModalLabel">
              <i class="bi bi-check-circle-fill me-2"></i>Payment Successful
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Your payment has been processed successfully!</p>
            <div v-if="paymentData">
              <p class="mb-1"><strong>Reference:</strong> {{ paymentData.reference }}</p>
              <p class="mb-1"><strong>Date:</strong> {{ formatDate(paymentData.date_paiement) }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Sidebar from '../panel/Sidebar.vue';
import Navbar from '../panel/Navbar.vue';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

export default {
  name: 'Reservation',
  components: {
    Sidebar,
    Navbar
  },
  setup() {
    // Create refs for component state
    const reservations = ref([]);
    const loading = ref(true);
    const error = ref('');
    const client = ref(null);
    const paymentData = ref(null);
    const processingPayments = ref([]);
    const currentDate = ref(new Date().toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }));
    
    // Create axios instance within setup
    const apiClient = axios.create({
      baseURL: 'http://localhost:8000/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    // Set auth token if exists
    const token = localStorage.getItem('token');
    if (token) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }

    // Get client data from localStorage
    const getClient = () => {
      const clientData = localStorage.getItem('client');
      if (clientData) {
        client.value = JSON.parse(clientData);
        return client.value;
      }
      return null;
    };

    const fetchReservations = async () => {
      loading.value = true;
      error.value = '';
      
      const userData = getClient();
      
      if (!userData || !userData.id) {
        error.value = 'User data not found. Please login again.';
        loading.value = false;
        return;
      }
      
      try {
        // Using the apiClient instance created in setup
        const response = await apiClient.get(`/front-office/clients/${userData.id}/reservations`);
        
        if (response.data && response.data.data) {
          reservations.value = response.data.data;
        } else {
          error.value = 'Invalid data structure returned from API';
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load reservations. Please try again later.';
        console.error('Error fetching reservations:', err);
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (dateString) => {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'MGA' 
      }).format(amount);
    };

    const getStatusBadgeClass = (status) => {
      switch (status.toLowerCase()) {
        case 'fait':
          return 'bg-success';
        case 'annulé':
          return 'bg-danger';
        case 'en attente':
          return 'bg-warning text-dark';
        case 'confirmé':
          return 'bg-info text-dark';
        case 'payé':
          return 'bg-success';
        default:
          return 'bg-secondary';
      }
    };

    const getStatusIconClass = (status) => {
      switch (status.toLowerCase()) {
        case 'fait':
          return 'bi-check-circle-fill me-1';
        case 'annulé':
          return 'bi-x-circle-fill me-1';
        case 'en attente':
          return 'bi-hourglass-split me-1';
        case 'confirmé':
          return 'bi-check2-all me-1';
        case 'payé':
          return 'bi-credit-card-fill me-1';
        default:
          return 'bi-circle-fill me-1';
      }
    };
    
    const showPayButton = (reservation) => {
      console.log('RESA: ', reservation)
      return reservation.status.toLowerCase() === 'confirmé' 
          || reservation.status.toLowerCase() === 'a payer'
          || reservation.status.toLowerCase() === 'fait';
    };
    
    const isProcessingPayment = (reservationId) => {
      return processingPayments.value.includes(reservationId);
    };
    
    const processPayment = async (reservation) => {
      console.log('process resa', reservation);
      
      const userData = getClient();
      
      if (!userData || !userData.id) {
        error.value = 'User data not found. Please login again.';
        return;
      }
      
      // Add to processing state
      processingPayments.value.push(reservation.id_reservation);
      
      try {
        const response = await apiClient.post(
          `/front-office/reservations/${reservation.id_reservation}/pay`, 
          {
            id_client: userData.id,
            id_reservation: reservation.id_reservation
          }
        );
        
        if (response.data && response.data.data) {
          // Update payment data for modal
          paymentData.value = response.data.data;
          
          // Update reservation status in the list
          const index = reservations.value.findIndex(r => r.id === reservation.id_reservation);
          if (index !== -1) {
            reservations.value[index].status = 'Payé';
          }
          
          // Show success modal
          showPaymentSuccessModal();
        }
      } catch (err) {
        console.error('Payment error:', err);
        alert(err.response?.data?.message || 'Payment processing failed. Please try again.');
      } finally {
        // Remove from processing state
        processingPayments.value = processingPayments.value.filter(id => id !== reservation.id_reservation);
      }
    };
    
    const showPaymentSuccessModal = () => {
      // Use Bootstrap modal
      const modal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
      modal.show();
    };
    
    const logout = () => {
      localStorage.removeItem('token');
      localStorage.removeItem('client');
      window.location.href = '/';
    };

    onMounted(() => {
      fetchReservations();
      getClient();
      
      // Import Bootstrap JavaScript
      import('bootstrap');
    });

    return {
      reservations,
      loading,
      error,
      client,
      currentDate,
      paymentData,
      formatDate,
      formatCurrency,
      getStatusBadgeClass,
      getStatusIconClass,
      showPayButton,
      processPayment,
      isProcessingPayment,
      logout
    };
  }
};
</script>

<style scoped>
.home-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  font-family: 'Poppins', sans-serif;
}

/* Main Content Area */
.main-content {
  padding: 2rem 0;
}

/* Dashboard Header */
.dashboard-header {
  margin-bottom: 1.5rem;
}

.dashboard-header h2 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0.3rem;
}

.dashboard-date {
  color: #6c757d;
  font-size: 0.9rem;
}

/* Profile Card for Reservations */
.profile-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.profile-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.profile-header h4 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0;
}

.profile-body {
  padding: 1.5rem;
}

/* Table Styling */
.table {
  margin-bottom: 0;
}

.table th {
  font-weight: 600;
  color: #495057;
  border-top: none;
}

.table td {
  vertical-align: middle;
}

.badge {
  padding: 0.5rem 0.75rem;
  font-weight: 500;
  border-radius: 0.375rem;
}

.badge.bg-light {
  background-color: #f8f9fa !important;
  border: 1px solid #e9ecef;
}

/* Alert Styling */
.alert {
  border-radius: 8px;
  padding: 1rem;
}
</style>