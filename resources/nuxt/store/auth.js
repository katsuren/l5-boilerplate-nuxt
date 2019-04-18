export const state = () => ({
  jwt: null,
  user: null,
  lastPath: null,
})

export const getters = {
  isAuthenticated (state) {
    return !(state.jwt === null);
  },

  getUser (state) {
    return state.user;
  },

  getLastPath (state) {
    const path = state.lastPath;
    state.lastPath = null;
    return path;
  },
}

export const mutations = {
  SET_JWT (state, jwt) {
    state.jwt = jwt;
    if (jwt) {
      this.$axios.defaults.headers.common['Authorization'] = 'Bearer ' + jwt;
    } else {
      delete this.$axios.defaults.headers.common['Authorization'];
    }
  },

  SET_USER (state, user) {
    state.user = user;
  },

  SET_LASTPATH (state, path) {
    state.lastPath = path;
  },
}

export const actions = {
  async login ({ commit, dispatch }, { email, password }) {
    const response = await this.$axios.post('/api/login', {
      email: email,
      password: password,
    });
    const token = response.data.token;
    commit('SET_JWT', token);

    dispatch('fetchUser');
  },

  async fetchUser ({ commit }) {
    const response = await this.$axios.get('/api/account');
    const user = response.data.user;
    commit('SET_USER', user);
  },

  async unauthenticated ({ commit, dispatch }) {
    dispatch('logout');
    const path = window.location.href.replace(window.location.origin + '/pages', '');
    if (path !== '/login') {
      commit('SET_LASTPATH', path);
      this.$router.push('/login');
    }
  },

  logout ({ commit }) {
    commit('SET_JWT', null);
    commit('SET_USER', null);
  },
}
