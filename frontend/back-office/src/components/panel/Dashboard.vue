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
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Welcome to the Admin Dashboard</h5>
                  <p class="card-text">This is your control center. Use the sidebar to navigate through different sections.</p>
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
import Sidebar from './Sidebar.vue'

export default {
  name: 'Dashboard',
  components: {
    Sidebar
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
</style>