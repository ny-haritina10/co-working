<template>
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <i class="bi bi-buildings me-2"></i>
        <span>CoSpace</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3">
            <span class="welcome-text">
              <i class="bi bi-person-circle me-1"></i>
              Welcome, {{ client?.name_client }}
            </span>
          </li>
          <li class="nav-item">
            <button class="btn logout-btn" @click="handleLogout">
              <i class="bi bi-box-arrow-right me-1"></i>
              Sign Out
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
  props: {
    client: {
      type: Object,
      default: () => null
    }
  },
  methods: {
    handleLogout() {
      this.$emit('logout');
    }
  }
}
</script>

<style scoped>
.navbar {
  background: white;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  padding: 1rem 0;
}

.navbar-brand {
  font-weight: 600;
  font-size: 1.5rem;
  color: #2b3a4a;
}

.navbar-brand i {
  color: #4e73df;
  font-size: 1.3rem;
}

.welcome-text {
  color: #495057;
  font-weight: 500;
}

.logout-btn {
  background: #f8f9fa;
  color: #4e73df;
  border: none;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: #e9ecef;
  color: #224abe;
}
</style>