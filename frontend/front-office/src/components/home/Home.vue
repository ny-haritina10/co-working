<template>
  <div class="home-container">
    <!-- Navbar -->
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
              <button class="btn logout-btn" @click="logout">
                <i class="bi bi-box-arrow-right me-1"></i>
                Sign Out
              </button>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container main-content">
      <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
          <div class="sidebar-card">
            <div class="text-center mb-4">
              <div class="avatar-container">
                <i class="bi bi-person-circle"></i>
              </div>
              <h4 class="mt-3 user-name">{{ client?.name_client }}</h4>
              <p class="user-role">Member</p>
            </div>
            
            <div class="sidebar-menu">
              <a href="#" class="sidebar-menu-item active">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
              </a>
              <a href="#" class="sidebar-menu-item">
                <i class="bi bi-calendar-week me-2"></i>
                Bookings
              </a>
              <a href="#" class="sidebar-menu-item">
                <i class="bi bi-building me-2"></i>
                Spaces
              </a>
              <a href="#" class="sidebar-menu-item">
                <i class="bi bi-people me-2"></i>
                Community
              </a>
              <a href="#" class="sidebar-menu-item">
                <i class="bi bi-gear me-2"></i>
                Settings
              </a>
            </div>
          </div>
        </div>
        
        <!-- Main Dashboard -->
        <div class="col-lg-9">
          <div class="dashboard-header">
            <h2>My Dashboard</h2>
            <p class="dashboard-date">{{ currentDate }}</p>
          </div>
          
          <!-- Stats Cards -->
          <div class="row stats-row">
            <div class="col-md-4 mb-4">
              <div class="stats-card">
                <div class="stats-icon blue">
                  <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stats-info">
                  <h3>0</h3>
                  <p>Active Bookings</p>
                </div>
              </div>
            </div>
            
            <div class="col-md-4 mb-4">
              <div class="stats-card">
                <div class="stats-icon purple">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div class="stats-info">
                  <h3>0</h3>
                  <p>Hours Used</p>
                </div>
              </div>
            </div>
            
            <div class="col-md-4 mb-4">
              <div class="stats-card">
                <div class="stats-icon green">
                  <i class="bi bi-credit-card"></i>
                </div>
                <div class="stats-info">
                  <h3>$0</h3>
                  <p>Credits</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Profile Info -->
          <div class="profile-card">
            <div class="profile-header">
              <h4><i class="bi bi-person-badge me-2"></i>Profile Information</h4>
              <button class="btn edit-btn"><i class="bi bi-pencil"></i></button>
            </div>
            
            <div class="profile-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="profile-item">
                    <span class="profile-label">Full Name</span>
                    <span class="profile-value">{{ client?.name_client }}</span>
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <div class="profile-item">
                    <span class="profile-label">Phone Number</span>
                    <span class="profile-value">{{ client?.numero_client }}</span>
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <div class="profile-item">
                    <span class="profile-label">Member Since</span>
                    <span class="profile-value">February 2025</span>
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <div class="profile-item">
                    <span class="profile-label">Membership Type</span>
                    <span class="profile-value">Standard</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  data() {
    return {
      client: null,
      currentDate: new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  },
  created() {
    const clientData = localStorage.getItem('client')
    if (clientData) {
      this.client = JSON.parse(clientData)
    } else {
      this.$router.push('/')
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('token')
      localStorage.removeItem('client')
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
.home-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  font-family: 'Poppins', sans-serif;
}

/* Navbar Styling */
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

/* Main Content Area */
.main-content {
  padding: 2rem 0;
}

/* Sidebar Styling */
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

/* Dashboard Header */
.dashboard-header {
  margin-bottom: 1.5rem;
}

.dashboard-header h2 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0.3rem;
}

.dashboard-date {
  color: #6c757d;
  font-size: 0.9rem;
}

/* Stats Cards */
.stats-row {
  margin-bottom: 1.5rem;
}

.stats-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  transition: transform 0.2s ease;
}

.stats-card:hover {
  transform: translateY(-5px);
}

.stats-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
}

.stats-icon i {
  font-size: 1.5rem;
  color: white;
}

.stats-icon.blue {
  background: linear-gradient(45deg, #4e73df, #224abe);
}

.stats-icon.purple {
  background: linear-gradient(45deg, #7764e4, #5a4cb1);
}

.stats-icon.green {
  background: linear-gradient(45deg, #36b9cc, #258391);
}

.stats-info h3 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0.2rem;
}

.stats-info p {
  color: #6c757d;
  font-size: 0.9rem;
  margin-bottom: 0;
}

/* Profile Card */
.profile-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.profile-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.profile-header h4 {
  font-weight: 600;
  color: #2b3a4a;
  margin-bottom: 0;
}

.edit-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f1f5ff;
  color: #4e73df;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
}

.profile-body {
  padding: 1.5rem;
}

.profile-item {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.profile-label {
  color: #6c757d;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
}

.profile-value {
  font-weight: 500;
  color: #2b3a4a;
}
</style>