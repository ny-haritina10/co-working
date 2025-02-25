<!-- src/components/panel/Dashboard.vue -->
<template>
  <div class="d-flex">
    <Sidebar />
    <div class="flex-grow-1">
      <header class="header p-3">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="mb-0">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
          </h1>
          <div class="user-info">
            <span class="me-2">Welcome, Admin</span>
            <i class="bi bi-person-circle"></i>
          </div>
        </div>
      </header>
      <main class="p-4">
        <div class="container-fluid">
          <!-- Welcome Section -->
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Bienvenue sur le Dashboard Admin</h5>
                  <p class="card-text">Ceci est votre centre de contrôle. Utilisez la barre latérale pour naviguer entre les différentes sections.</p>
                </div>
              </div>
            </div>
          </div> <br>

          <!-- Total Revenue Section -->
          <div v-if="isLoading && !periodicRevenue.revenue" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-if="errorMessage" class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ errorMessage }}
          </div>
          <div v-else class="row mb-4">
            <div class="col-md-4">
              <div class="card shadow-sm revenue-card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <i class="bi bi-currency-dollar display-6 me-3 text-success"></i>
                    <div>
                      <h5 class="card-title">Revenus Payés</h5>
                      <p class="card-text fw-bold">{{ formatCurrency(revenueData.paid_revenue) }} Ar</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm revenue-card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <i class="bi bi-currency-dollar display-6 me-3 text-warning"></i>
                    <div>
                      <h5 class="card-title">Revenus Non Payés</h5>
                      <p class="card-text fw-bold">{{ formatCurrency(revenueData.unpaid_revenue) }} Ar</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm revenue-card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <i class="bi bi-currency-dollar display-6 me-3 text-primary"></i>
                    <div>
                      <h5 class="card-title">Revenus Total</h5>
                      <p class="card-text fw-bold">{{ formatCurrency(revenueData.total_revenue) }} Ar</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Periodic Revenue Section -->
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">
                    <i class="bi bi-calendar-range me-2"></i>
                    Revenus Périodiques
                  </h5>
                  <form @submit.prevent="fetchPeriodicRevenue" class="row g-3 align-items-end">
                    <div class="col-md-5">
                      <label for="dateMin" class="form-label">
                        <i class="bi bi-calendar-minus me-2 text-muted"></i>Date Début
                      </label>
                      <input
                        type="date"
                        class="form-control"
                        id="dateMin"
                        v-model="dateMin"
                        required
                      >
                    </div>
                    <div class="col-md-5">
                      <label for="dateMax" class="form-label">
                        <i class="bi bi-calendar-plus me-2 text-muted"></i>Date Fin
                      </label>
                      <input
                        type="date"
                        class="form-control"
                        id="dateMax"
                        v-model="dateMax"
                        required
                      >
                    </div>
                    <div class="col-md-2">
                      <button
                        type="submit"
                        class="btn btn-primary w-100"
                        :disabled="isFetchingPeriodic"
                      >
                        <span v-if="isFetchingPeriodic" class="spinner-border spinner-border-sm me-2" role="status"></span>
                        {{ isFetchingPeriodic ? 'Chargement...' : 'Afficher' }}
                      </button>
                    </div>
                  </form>
                  <div v-if="periodicRevenue.revenue" class="mt-4">
                    <div class="row">
                      <div class="col-md-6">
                        <p class="card-text">
                          <i class="bi bi-calendar-minus me-2 text-muted"></i>
                          <strong>Date Début:</strong> {{ formatDate(periodicRevenue.date_min) }}
                        </p>
                        <p class="card-text">
                          <i class="bi bi-calendar-plus me-2 text-muted"></i>
                          <strong>Date Fin:</strong> {{ formatDate(periodicRevenue.date_max) }}
                        </p>
                      </div>
                      <div class="col-md-6 text-md-end">
                        <p class="card-text fw-bold fs-4">
                          <i class="bi bi-currency-dollar me-2 text-info"></i>
                          {{ formatCurrency(periodicRevenue.revenue) }} Ar
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Time Slots Section -->
          <div v-if="!isLoading && !errorMessage" class="row mb-4">
            <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">
                    <i class="bi bi-clock-history me-2"></i>
                    Top Créneaux Horaires
                  </h5>
                  <p class="card-text text-muted mb-3">{{ timeSlotsMessage }}</p>
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col"><i class="bi bi-clock me-2"></i>Heure</th>
                        <th scope="col"><i class="bi bi-bar-chart-fill me-2"></i>Nombre de Réservations</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(slot, index) in topTimeSlots" :key="index">
                        <td>{{ slot.time }}</td>
                        <td>{{ slot.count }}</td>
                      </tr>
                      <tr v-if="topTimeSlots.length === 0">
                        <td colspan="2" class="text-center">Aucun créneau horaire populaire trouvé</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Sidebar from './Sidebar.vue'

export default {
  name: 'Dashboard',
  components: {
    Sidebar
  },
  data() {
    return {
      revenueData: {
        paid_revenue: 0,
        unpaid_revenue: 0,
        total_revenue: 0
      },
      periodicRevenue: {
        date_min: '',
        date_max: '',
        revenue: 0
      },
      topTimeSlots: [],
      timeSlotsMessage: '',
      dateMin: '2025-01-01',
      dateMax: '2025-05-05',
      isLoading: false,
      isFetchingPeriodic: false,
      errorMessage: '',
      apiClient: axios.create({
        baseURL: 'http://127.0.0.1:8000/api',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('adminToken')}`
        }
      })
    }
  },
  created() {
    this.fetchRevenueStats()
    this.fetchPeriodicRevenue()
    this.fetchTopTimeSlots()
  },
  methods: {
    async fetchRevenueStats() {
      try {
        this.isLoading = true
        this.errorMessage = ''

        const response = await this.apiClient.get('/back-office/statistics/total-revenue')
        this.revenueData = response.data
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors du chargement des statistiques de revenus'
      } finally {
        this.isLoading = false
      }
    },
    async fetchPeriodicRevenue() {
      try {
        this.isFetchingPeriodic = true
        this.errorMessage = ''

        const response = await this.apiClient.get('/back-office/statistics/periodic-revenue', {
          params: {
            date_min: this.dateMin,
            date_max: this.dateMax
          }
        })
        this.periodicRevenue = response.data
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors du chargement des revenus périodiques'
      } finally {
        this.isFetchingPeriodic = false
      }
    },
    async fetchTopTimeSlots() {
      try {
        this.isLoading = true
        this.errorMessage = ''

        const response = await this.apiClient.get('/back-office/statistics/top-time-slots')
        this.topTimeSlots = response.data.top_time_slots
        this.timeSlotsMessage = response.data.message
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors du chargement des créneaux horaires populaires'
      } finally {
        this.isLoading = false
      }
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('fr-FR').format(amount)
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }
  }
}
</script>

<style scoped>
.header {
  background-color: #2c3e50;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

h1 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 1.5rem;
}

.user-info {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

main {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: calc(100vh - 70px);
}

.card {
  border-radius: 15px;
  border: none;
}

.revenue-card {
  background-color: white;
  transition: transform 0.3s ease;
}

.revenue-card:hover {
  transform: translateY(-5px);
}

.card-title {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.card-text {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #34495e;
}

.card-text.fw-bold.fs-4 {
  color: #2c3e50;
}

.form-label {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
}

.btn-primary {
  background-color: #2c3e50;
  border-color: #2c3e50;
}

.btn-primary:hover:not(:disabled) {
  background-color: #34495e;
  border-color: #34495e;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.table {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.table thead th {
  background-color: #2c3e50;
  color: white;
}

.text-success {
  color: #28a745 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-primary {
  color: #007bff !important;
}

.text-info {
  color: #17a2b8 !important;
}

.text-muted {
  color: #6c757d !important;
}
</style>