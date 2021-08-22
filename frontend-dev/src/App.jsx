/* eslint-disable no-undef */
/* eslint-disable react/jsx-props-no-spreading */
/* eslint-disable no-console */
/* eslint-disable react/jsx-one-expression-per-line */

import { lazy, Suspense } from 'react'
import { BrowserRouter as Router, Switch, Route, NavLink, Link } from 'react-router-dom'
import './resource/sass/app.scss'
// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from './Utils/i18nwrap'
import './resource/icons/style.css'
import Loader from './components/Loaders/Loader'
import logo from './resource/img/integ/crm.svg'
import Integrations from "./components/Integrations"
import TableLoader from './components/Loaders/TableLoader'

const AllForms = lazy(() => import('./pages/AllForms'))
const Error404 = lazy(() => import('./pages/Error404'))

function App() {  
  const loaderStyle = { height: '90vh' }

  return (
    <Suspense fallback={(<Loader className="g-c" style={loaderStyle} />)}>
      <Router basename={typeof bitwpfzc !== 'undefined' ? bitwpfzc.baseURL : '/'}>
        <div className="Btcd-App">

          <div className="nav-wrp">
            <div className="flx">
              <div className="logo flx" title={__('Integrations for wpforms', 'bitwpfzc')}>
                <Link to="/" className="flx">
                  <img src={logo} alt="logo" className="ml-2" />
                  <span className="ml-2">Integrations for wpforms</span>
                </Link>
              </div>
              <nav className="top-nav ml-2">
                <NavLink
                  exact
                  to="/"
                  activeClassName="app-link-active"
                >
                  {__('My Forms', 'bitwpfzc')}
                </NavLink>
              </nav>
            </div>
          </div>

          <div className="route-wrp">
            <Switch>
              <Route exact path="/">
                <Suspense fallback={<TableLoader />}>
                  <AllForms/>
                </Suspense>
              </Route>
              <Route path="/form/:formID/integrations">
                <Suspense fallback={<Loader className="g-c" style={loaderStyle} />}>
                  <Integrations/>
                </Suspense>
              </Route>
              <Route path="*">
                <Error404 />
              </Route>
            </Switch>
          </div>
        </div>
        </Router>
    </Suspense>
  )
}

export default App
