import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env?.VITE_API_URL || "http://localhost:8000/api",
  timeout: 15000,
});

// Adjunta el token en cada request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  } else {
    delete config.headers.Authorization;
  }
  return config;
});

// NO desloguear automáticamente por cualquier 401.
// Deja que AuthContext decida (basado en /me).
api.interceptors.response.use(
  (r) => r,
  (error) => Promise.reject(error)
);

export default api;
