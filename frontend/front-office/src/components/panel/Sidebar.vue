<template>
  <div class="sidebar-card">
    <div class="text-center mb-4">
      <div class="avatar-container">
        <i class="bi bi-person-circle"></i>
      </div>
      <h4 class="mt-3 user-name">{{ client?.name_client }}</h4>
      <p class="user-role">Member</p>
    </div>
    
    <div class="sidebar-menu">
      <router-link to="/home" class="sidebar-menu-item" :class="{ 'active': currentPage === 'home' }">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
      </router-link>
      <router-link to="/reservation" class="sidebar-menu-item" :class="{ 'active': currentPage === 'reservation' }">
        <i class="bi bi-calendar-week me-2"></i>
        Bookings
      </router-link>
      <router-link to="/espace" class="sidebar-menu-item" :class="{ 'active': currentPage === 'espace' }">
        <i class="bi bi-building me-2"></i>
        Spaces
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Sidebar',
  props: {
    client: {
      type: Object,
      default: () => null
    },
    currentPage: {
      type: String,
      default: 'dashboard'
    }
  }
}
</script>

<style scoped>
.sidebar-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  padding: 2rem 1.5rem;
}

.avatar-container {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  background: #f1f5ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-container i {
  font-size: 2.5rem;
  color: #4e73df;
}

.user-name {
  font-weight: 600;
  margin-bottom: 0.2rem;
  color: #2b3a4a;
}

.user-role {
  color: #6c757d;
  margin-bottom: 1.5rem;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
}

.sidebar-menu-item {
  padding: 0.8rem 1rem;
  color: #495057;
  text-decoration: none;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  transition: all 0.2s ease;
  font-weight: 500;
}

.sidebar-menu-item:hover, .sidebar-menu-item.active {
  background: #f1f5ff;
  color: #4e73df;
}

.sidebar-menu-item.active {
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(78, 115, 223, 0.15);
}
</style>