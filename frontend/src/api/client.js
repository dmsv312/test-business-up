import axios from 'axios'

// Базовый адрес /api проксируется Vite на Laravel в dev и обслуживается nginx в prod.
export default axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})
