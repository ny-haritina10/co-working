<!-- src/components/AdminLogin.vue -->
<template>
  <div class="login-container">
    <div class="card shadow-lg">
      <div class="card-body p-5">
        <h2 class="card-title text-center mb-4">
          <i class="bi bi-lock-fill me-2"></i>Admin Login
        </h2>
        
        <form @submit.prevent="handleLogin">
          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-person-fill"></i>
              </span>
              <input
                v-model="form.email"
                type="email"
                class="form-control"
                placeholder="Email"
                required
              >
            </div>
          </div>

          <div class="mb-4">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-key-fill"></i>
              </span>
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                placeholder="Password"
                required
              >
            </div>
          </div>

          <div v-if="errorMessage" class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 mt-3"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminLogin',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errorMessage: '',
      isLoading: false
    }
  },
  created() {
    this.apiClient = axios.create({
      baseURL: 'http://127.0.0.1:8000/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
  },
  methods: {
    async handleLogin() {
      try {
        this.isLoading = true
        this.errorMessage = ''

        const response = await this.apiClient.post('/admin/login', {
          email: this.form.email,
          password: this.form.password
        })

        if (response.data.token) {
          localStorage.setItem('adminToken', response.data.token)
          this.form.email = ''
          this.form.password = ''
          
          this.$router.push('/dashboard') 
        }
      } catch (error) {
        if (error.response) {
          this.errorMessage = error.response.data.message || 'An error occurred during login'
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
.login-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.card {
  min-width: 400px;
  border: none;
  border-radius: 15px;
}

.card-title {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
  font-weight: 600;
}

.form-control {
  border-radius: 0 0.375rem 0.375rem 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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