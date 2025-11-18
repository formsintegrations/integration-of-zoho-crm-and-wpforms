import { lazy, Suspense } from 'react'
import ReactDOM from 'react-dom/client'
import { AllFormContextProvider } from './Utils/AllFormContext'
import Loader from './components/Loaders/Loader'
import 'regenerator-runtime/runtime'

const App = lazy(() => import('./App'))

const container = document.getElementById('btcd-app')

const root = ReactDOM.createRoot(container)

root.render(
  <AllFormContextProvider>
    <Suspense
      fallback={
        <Loader
          style={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            height: '82vh'
          }}
        />
      }
    >
      <App />
    </Suspense>
  </AllFormContextProvider>
)
