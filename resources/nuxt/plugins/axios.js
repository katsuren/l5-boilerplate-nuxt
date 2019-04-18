export default function ({ $axios, store }) {
  $axios.onRequest(config => {
    // console.log('Making request to ' + config.url)
  })

  $axios.onError(error => {
    console.error(error);
    const status = parseInt(error.response && error.response.status);
    if (process.client && status === 401) {
      store.dispatch('auth/unauthenticated');
    }
  })

  if (process.client) {
    $axios.defaults.baseURL = window.location.origin;
    $axios.defaults.browserBaseURL = window.location.origin;
    $axios.defaults.headers['X-Requested-With'] = 'XMLHttpRequest';
  }
}
