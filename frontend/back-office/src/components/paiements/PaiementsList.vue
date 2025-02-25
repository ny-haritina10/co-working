<!-- src/components/paiements/PaiementsList.vue -->
<template>
  <div class="d-flex">
    <Sidebar />
    <div class="flex-grow-1">
      <header class="header p-3">
        <h1 class="mb-0">
          <i class="bi bi-cash me-2"></i>
          Liste des Paiements
        </h1>
      </header>
      <main class="p-4">
        <div class="container-fluid">
          <div class="card shadow-lg">
            <div class="card-body p-5">
              <div v-if="isLoading" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-if="successMessage" class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ successMessage }}
              </div>
              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ errorMessage }}
              </div>
              <div v-else-if="!isLoading">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th scope="col"><i class="bi bi-hash me-2"></i>ID</th>
                      <th scope="col"><i class="bi bi-tag-fill me-2"></i>Référence</th>
                      <th scope="col"><i class="bi bi-calendar-check me-2"></i>Date Paiement</th>
                      <th scope="col"><i class="bi bi-check-square me-2"></i>Statut</th>
                      <th scope="col"><i class="bi bi-gear me-2"></i>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="paiement in paiements" :key="paiement.id">
                      <td>PAI-00{{ paiement.id }}</td>
                      <td>{{ paiement.reference }}</td>
                      <td>{{ formatDate(paiement.date_paiement) }}</td>
                      <td>{{ paiement.validated_at ? formatDate(paiement.validated_at) : 'Non validé' }}</td>
                      <td>
                        <button
                          class="btn btn-primary me-2"
                          @click.prevent="validatePaiement(paiement.id)"
                          :disabled="paiement.validated_at || isValidating[paiement.id]"
                        >
                          <span v-if="isValidating[paiement.id]" class="spinner-border spinner-border-sm me-2" role="status"></span>
                          {{ isValidating[paiement.id] ? 'Validation...' : 'Validé' }}
                        </button>
                        <button
                          class="btn btn-info text-white"
                          @click="showDetails(paiement)"
                          data-bs-toggle="modal"
                          data-bs-target="#reservationModal"
                        >
                          <i class="bi bi-eye-fill me-2"></i>Détails Réservations
                        </button>
                      </td>
                    </tr>
                    <tr v-if="paiements.length === 0">
                      <td colspan="6" class="text-center">Aucun paiement trouvé</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Reservation Details Modal -->
      <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="reservationModalLabel">
                <i class="bi bi-calendar-event me-2"></i>
                Détails de la Réservation RES-00{{ selectedPaiement?.id_reservation }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div v-if="selectedPaiement" class="row">
                <div class="col-md-6">
                  <h6><i class="bi bi-info-circle me-2"></i>Informations Générales</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                      <strong>ID Réservation:</strong> RES-00{{ selectedPaiement.id_reservation }}
                    </li>
                    <li class="list-group-item">
                      <strong>Référence:</strong> {{ selectedPaiement.reservation.reference }}
                    </li>
                    <li class="list-group-item">
                      <strong>Espace ID:</strong> {{ selectedPaiement.reservation.id_espace }}
                    </li>
                    <li class="list-group-item">
                      <strong>Client ID:</strong> {{ selectedPaiement.reservation.id_client }}
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6><i class="bi bi-clock me-2"></i>Dates et Durée</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                      <strong>Date de Réservation:</strong> {{ formatDate(selectedPaiement.reservation.datetime_reservation) }}
                    </li>
                    <li class="list-group-item">
                      <strong>Durée (heures):</strong> {{ selectedPaiement.reservation.hour_duration }}
                    </li>
                    <li class="list-group-item">
                      <strong>Créé le:</strong> {{ formatDate(selectedPaiement.reservation.created_at) }}
                    </li>
                    <li class="list-group-item">
                      <strong>Mis à jour le:</strong> {{ formatDate(selectedPaiement.reservation.updated_at) }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Sidebar from '../panel/Sidebar.vue'

export default {
  name: 'PaiementsList',
  components: {
    Sidebar
  },
  data() {
    return {
      paiements: [],
      isLoading: false,
      isValidating: {},
      successMessage: '',
      errorMessage: '',
      selectedPaiement: null,
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
    this.fetchPaiements()
  },
  methods: {
    async fetchPaiements() {
      try {
        this.isLoading = true
        this.errorMessage = ''
        this.successMessage = ''

        const response = await this.apiClient.get('/back-office/paiements')
        console.log(response)

        if (response.data.success) {
          this.paiements = response.data.data
          this.isValidating = this.paiements.reduce((acc, paiement) => {
            acc[paiement.id] = false
            return acc
          }, {})
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors du chargement des paiements'
      } finally {
        this.isLoading = false
      }
    },
    async validatePaiement(id_paiement) {
      try {
        this.isValidating[id_paiement] = true
        this.errorMessage = ''
        this.successMessage = ''

        const response = await this.apiClient.put(`/back-office/paiements/${id_paiement}/validate`)

        if (response.data.success) {
          this.successMessage = response.data.message || 'Paiement validé avec succès'
          const index = this.paiements.findIndex(p => p.id === id_paiement)
          if (index !== -1) {
            this.paiements[index].validated_at = new Date().toISOString()
          }
          setTimeout(() => {
            this.successMessage = ''
          }, 5000)
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors de la validation du paiement'
        setTimeout(() => {
          this.errorMessage = ''
        }, 5000)
      } finally {
        this.isValidating[id_paiement] = false
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      })
    },
    showDetails(paiement) {
      this.selectedPaiement = paiement
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

main {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: calc(100vh - 70px);
}

.card {
  border-radius: 15px;
  border: none;
}

.table {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

thead th {
  background-color: #2c3e50;
  color: white;
}

.table-hover tbody tr:hover {
  background-color: #f5f7fa;
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

.btn-info {
  background-color: #17a2b8;
  border-color: #17a2b8;
}

.btn-info:hover {
  background-color: #138496;
  border-color: #138496;
}

.alert {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.modal-content {
  border-radius: 15px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.modal-header {
  background-color: #2c3e50;
  color: white;
}

.modal-title {
  font-weight: 600;
}

.list-group-item {
  border: none;
  padding: 10px 0;
}

.list-group-item strong {
  color: #2c3e50;
}
</style>