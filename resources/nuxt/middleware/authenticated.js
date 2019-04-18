export default function({ store, redirect }) {
  if (!store.getters['auth/isAuthenticated']) {
    store.dispatch('auth/unauthenticated');
    redirect('/login');
  }
}
