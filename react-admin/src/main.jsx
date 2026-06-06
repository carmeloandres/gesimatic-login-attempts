import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { GesimaticLoginAttemptsApp } from './GesimaticLoginAttemptsApp.jsx'

createRoot(document.getElementById('gesimatic-login-attempts-admin')).render(
  <StrictMode>
    <GesimaticLoginAttemptsApp />
  </StrictMode>,
)
