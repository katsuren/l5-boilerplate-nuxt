<template>
  <v-container>
    <v-form>
      <v-card>
        <v-alert
          :value="error"
          type="error"
        >
          IDとパスワードの組みが正しくありません
        </v-alert>
        <v-card-title primary-title>
          <h3 class="headline mb-0">ログイン</h3>
        </v-card-title>
        <v-card-text>
          <v-text-field 
            v-model="email"
            label="メールアドレス"
            type="email"
            required
          />
          <v-text-field
            v-model="password"
            label="パスワード"
            type="password"
            required
          />
        </v-card-text>
    
        <v-btn dark @click="submit">ログイン</v-btn>
      </v-card>
    </v-form>
  </v-container>
</template>

<script>
export default {
  middleware: [
    'guest',
  ],

  data() {
    return {
      email: '',
      password: '',
      error: false
    }
  },

  methods: {
    async submit () {
      await this.$store.dispatch("auth/login", {
        email: this.email,
        password: this.password
      }).then(() => {
        const next = this.$store.getters['auth/getLastPath'] || '/profile';
        this.$router.push(next)
      }).catch((e) => {
        // TODO: error handling
        this.error = true
      });
    },
  },
}
</script>
