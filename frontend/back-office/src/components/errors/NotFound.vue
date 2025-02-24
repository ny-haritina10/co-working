<!-- src/components/NotFound.vue -->
<template>
  <div class="not-found">
    <h1><i class="bi bi-exclamation-triangle-fill me-2"></i>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <router-link to="/dashboard" class="btn btn-primary">
      Back to Dashboard
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>

<style scoped>
.not-found {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h1 {
  color: #2c3e50;
}

.btn-primary {
  background-color: #2c3e50;
  border-color: #2c3e50;
}

.btn-primary:hover {
  background-color: #34495e;
  border-color: #34495e;
}
</style>