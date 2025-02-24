<!-- src/components/Sidebar.vue -->
<template>
  <div class="sidebar d-flex flex-column flex-shrink-0 p-3">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <i class="bi bi-bootstrap-fill me-2"></i>
      <span class="fs-4">Admin Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <router-link to="/dashboard" class="nav-link text-white" active-class="active">
          <i class="bi bi-house-door-fill me-2"></i>
          Dashboard
        </router-link>
      </li>
      <li>
        <a href="#" class="nav-link text-white">
          <i class="bi bi-person-fill me-2"></i>
          Users
        </a>
      </li>
      <li>
        <a href="#" class="nav-link text-white">
          <i class="bi bi-gear-fill me-2"></i>
          Settings
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
  </div>
</template>

<script>
export default {
  name: 'Sidebar',
  methods: {
    logout() {
      localStorage.removeItem('adminToken')
      this.$router.push('/login')
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
</style>