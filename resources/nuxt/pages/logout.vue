<template>
  <div>
  </div>
</template>

<script>
export default {
  created() {
    this.$store.dispatch("auth/logout");
    this.$router.push('/');
  },
}
</script>
