<template>
  <div>
    <h1>こんにちは、{{ this.user.name }}さん！！</h1>
    <a @click="hoge">hoge</a>
  </div>
</template>

<script>
export default {
  middleware: [
    'authenticated',
  ],

  data() {
    return {
      user: {},
    };
  },

  methods: {
    hoge () {
      this.$router.push('/inspire');
    },
  },

  created() {
    this.$store.dispatch('auth/fetchUser')
      .then(() => {
        this.user = this.$store.getters['auth/getUser'];
      });
  },
}
</script>
