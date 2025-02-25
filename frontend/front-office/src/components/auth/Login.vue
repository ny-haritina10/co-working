<template>
  <div class="login-container">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="login-card">
        <div class="brand-section text-center mb-4">
          <i class="bi bi-buildings display-4 text-primary"></i>
          <h1 class="brand-title">CoSpace</h1>
          <p class="brand-tagline">Your productive workspace awaits</p>
        </div>
        
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="mb-4">
            <label class="form-label">Phone Number</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-telephone"></i>
              </span>
              <input
                v-model="form.numero_client"
                type="text"
                class="form-control border-start-0"
                placeholder="Enter your phone number"
                required
              >
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
            <i class="bi bi-door-open me-2"></i>
            Enter Workspace
          </button>
        </form>
        
        <div v-if="error" class="alert alert-danger mt-4 d-flex align-items-center">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <span>{{ error }}</span>
        </div>
        
        <div class="text-center mt-4 help-section">
          <p>Need assistance? <a href="#" class="text-decoration-none">Contact support</a></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Login',
  data() {
    return {
      form: {
        numero_client: '',
        password: ''
      },
      error: '',
      apiClient: null
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
        const response = await this.apiClient.post('/client/auth', this.form)
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('client', JSON.stringify(response.data.client))
        this.$router.push('/home')
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed. Please check your credentials and try again.'
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

.login-card {
  background: white;
  max-width: 480px;
  width: 100%;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
  padding: 3rem 2.5rem;
  transition: transform 0.2s ease;
}

.login-card:hover {
  transform: translateY(-5px);
}

.brand-title {
  font-weight: 600;
  font-size: 2.2rem;
  color: #2b3a4a;
  margin-top: 0.5rem;
  margin-bottom: 0.2rem;
}

.brand-tagline {
  color: #6c757d;
  font-size: 1rem;
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

.input-group-text {
  color: #6c757d;
}

.form-control {
  padding: 0.8rem 1rem;
  background-color: #f8f9fa;
}

.form-control:focus {
  box-shadow: none;
  border-color: #4e73df;
  background-color: #fff;
}

.login-btn {
  background: linear-gradient(to right, #4e73df, #224abe);
  border: none;
  font-weight: 500;
  padding: 0.8rem;
  margin-top: 1rem;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.login-btn:hover {
  background: linear-gradient(to right, #224abe, #1a3997);
  box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
  transform: translateY(-2px);
}

.help-section {
  color: #6c757d;
  font-size: 0.9rem;
}

.help-section a {
  color: #4e73df;
  font-weight: 500;
}

.alert {
  border-radius: 10px;
  border-left: 4px solid #dc3545;
}

.login-form {
  margin-top: 1rem;
}
</style>