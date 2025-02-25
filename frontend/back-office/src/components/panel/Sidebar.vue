<template>
  <div class="sidebar d-flex flex-column flex-shrink-0 p-3">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <i class="bi bi-bootstrap-fill me-2"></i>
      <span class="fs-4">Back-Office</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <router-link to="/dashboard" class="nav-link text-white" active-class="active">
          <i class="bi bi-house-door-fill me-2"></i>
          Dashboard
        </router-link>
      </li>
      <li class="nav-item">
        <router-link to="/paiements" class="nav-link text-white" active-class="active">
          <i class="bi bi-cash-stack me-2"></i>
          Liste des Paiements
        </router-link>
      </li>
      <li class="nav-item">
        <div class="dropdown">
          <a
            class="nav-link text-white dropdown-toggle"
            href="#"
            id="importDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
            Import CSV
          </a>
          <ul class="dropdown-menu" aria-labelledby="importDropdown">
            <li>
              <router-link to="/import-espaces" class="dropdown-item text-dark">
                <i class="bi bi-building me-2"></i>Espaces
              </router-link>
            </li>
            <li>
              <router-link to="/import-reservations" class="dropdown-item text-dark">
                <i class="bi bi-calendar-check me-2"></i>Réservations
              </router-link>
            </li>
            <li>
              <router-link to="/import-options" class="dropdown-item text-dark">
                <i class="bi bi-gear me-2"></i>Options
              </router-link>
            </li>
            <li>
              <router-link to="/import-paiements" class="dropdown-item text-dark">
                <i class="bi bi-cash me-2"></i>Paiements
              </router-link>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-white" @click.prevent="resetDatabase">
          <i class="bi bi-arrow-repeat me-2"></i>
          Reset Database
        </a>
      </li>
    </ul>
    <hr>
    <div class="logout">
      <a href="#" class="nav-link text-white" @click.prevent="logout">
        <i class="bi bi-box-arrow-left me-2"></i>
        Logout
      </a>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="alert alert-success mt-3" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="alert alert-danger mt-3" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Sidebar',
  data() {
    return {
      successMessage: '',
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
  methods: {
    logout() {
      localStorage.removeItem('adminToken')
      this.$router.push('/login')
    },
    async resetDatabase() {
      // Show confirmation dialog
      const confirmed = window.confirm('Are you sure you want to reset the database? This action cannot be undone.')
      if (!confirmed) return

      try {
        this.successMessage = ''
        this.errorMessage = ''

        const response = await this.apiClient.delete('/back-office/reset-database')

        if (response.status === 200) {
          this.successMessage = response.data.message
          // Clear message after 5 seconds
          setTimeout(() => {
            this.successMessage = ''
          }, 5000)
        }
      } catch (error) {
        if (error.response) {
          this.errorMessage = `${error.response.data.error}: ${error.response.data.details}`
        } else {
          this.errorMessage = 'An unexpected error occurred while resetting the database'
        }
        // Clear message after 5 seconds
        setTimeout(() => {
          this.errorMessage = ''
        }, 5000)
      }
    }
  }
}
</script>

<style scoped>
.sidebar {
  background-color: #2c3e50;
  min-height: 100vh;
  width: 250px;
}

.nav-link {
  transition: all 0.3s ease;
}

.nav-link:hover {
  background-color: #34495e;
}

.active {
  background-color: #34495e;
}

hr {
  border-color: rgba(255, 255, 255, 0.2);
}

.fs-4 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dropdown-menu {
  background-color: #fff;
  border: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
}

.dropdown-item:hover {
  background-color: #f5f7fa;
}

.alert {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  position: fixed;
  bottom: 20px;
  left: 260px; /* Adjusted for sidebar width */
  z-index: 1000;
  max-width: 400px;
}
</style>