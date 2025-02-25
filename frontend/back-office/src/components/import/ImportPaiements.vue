<template>
  <div class="d-flex">
    <Sidebar />
    <div class="flex-grow-1">
      <header class="header p-3">
        <h1 class="mb-0">
          <i class="bi bi-cash me-2"></i>
          Import Paiements
        </h1>
      </header>
      <main class="p-4">
        <div class="container-fluid">
          <div class="card shadow-lg">
            <div class="card-body p-5">
              <form @submit.prevent="handleImport">
                <div class="mb-4">
                  <label for="csvFile" class="form-label">
                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>
                    Upload CSV File
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-upload"></i>
                    </span>
                    <input
                      type="file"
                      class="form-control"
                      id="csvFile"
                      accept=".csv"
                      @change="onFileChange"
                      required
                    >
                  </div>
                </div>

                <div v-if="errorMessage" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i>
                  {{ errorMessage }}
                  <ul v-if="errors.length">
                    <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                  </ul>
                </div>

                <div v-if="successMessage" class="alert alert-success" role="alert">
                  <i class="bi bi-check-circle-fill me-2"></i>
                  {{ successMessage }}
                </div>

                <button
                  type="submit"
                  class="btn btn-primary w-100 mt-3"
                  :disabled="isLoading"
                >
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  {{ isLoading ? 'Importing...' : 'Import CSV' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Sidebar from '../panel/Sidebar.vue'

export default {
  name: 'ImportPaiements',
  components: {
    Sidebar
  },
  data() {
    return {
      file: null,
      errorMessage: '',
      errors: [],
      successMessage: '',
      isLoading: false,
      apiClient: axios.create({
        baseURL: 'http://127.0.0.1:8000/api',
        headers: {
          'Content-Type': 'multipart/form-data',
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('adminToken')}`
        }
      })
    }
  },
  methods: {
    onFileChange(event) {
      this.file = event.target.files[0]
    },
    async handleImport() {
      if (!this.file) {
        this.errorMessage = 'Please select a CSV file'
        return
      }

      try {
        this.isLoading = true
        this.errorMessage = ''
        this.errors = []
        this.successMessage = ''

        const formData = new FormData()
        formData.append('file', this.file)

        const response = await this.apiClient.post('/back-office/paiements/import', formData)

        if (response.data.success) {
          this.successMessage = response.data.message
          this.file = null
          this.$refs.csvFile.value = '' // Reset file input
        }
      } catch (error) {
        console.log('error', error)
        if (error.response) {
          this.errorMessage = error.response.data.message || 'An error occurred during import'
          this.errors = error.response.data.errors ? Object.values(error.response.data.errors).flat() : []
        } else {
          this.errorMessage = 'An unexpected error occurred'
        }
      } finally {
        this.isLoading = false
      }
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

.form-label {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
  font-weight: 500;
}

.form-control {
  border-radius: 0 0.375rem 0.375rem 0;
}

.btn-primary {
  background-color: #2c3e50;
  border-color: #2c3e50;
  padding: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #34495e;
  border-color: #34495e;
}

.input-group-text {
  background-color: #f8f9fa;
}
</style>