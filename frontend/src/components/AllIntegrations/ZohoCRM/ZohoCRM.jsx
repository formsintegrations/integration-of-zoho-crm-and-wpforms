import { __ } from '../../../Utils/i18nwrap'
import { useEffect, useState } from 'react'
import 'react-multiple-select-dropdown-lite/dist/index.css'
import { useParams, useNavigate } from 'react-router-dom'
import SnackMsg from '../../Utilities/SnackMsg'
import Steps from '../../Utilities/Steps'
import { setGrantTokenResponse, saveIntegConfig } from '../IntegrationHelpers/IntegrationHelpers'
import IntegrationStepThree from '../IntegrationHelpers/IntegrationStepThree'
import ZohoCRMAuthorization from './ZohoCRMAuthorization'
import { checkMappedFields, handleInput, refreshModules } from './ZohoCRMCommonFunc'
import ZohoCRMIntegLayout from './ZohoCRMIntegLayout'

function ZohoCRM({ formFields, setIntegration, integrations, allIntegURL }) {
  const navigate = useNavigate()
  const { formID } = useParams()
  const [isLoading, setisLoading] = useState(false)
  const [step, setstep] = useState(1)
  const [snack, setSnackbar] = useState({ show: false })
  const [tab, settab] = useState(0)

  const [crmConf, setCrmConf] = useState({
    name: 'Zoho CRM API',
    type: 'Zoho CRM',
    clientId: process.env.NODE_ENV === 'development' ? '1000.1CM2UB5F7SEO6I7ZL5REIJ9CR8W7VT' : '',
    clientSecret:
      process.env.NODE_ENV === 'development' ? '3a0c2de3e33791ebde7d78b56bbbcf80eca7f28160' : '',
    module: '',
    layout: '',
    field_map: [{ formField: '', zohoFormField: '' }],
    relatedlists: [],
    actions: {}
  })

  useEffect(() => {
    window.opener && setGrantTokenResponse('zohoCRM')
  }, [])

  useEffect(() => {
    const scrollContainer = document.querySelector('.btcd-s-wrp')
    if (scrollContainer) {
      scrollContainer.scrollTop = 0
    }
  })

  const saveConfig = () => {
    const resp = saveIntegConfig(formID, integrations, setIntegration, allIntegURL, crmConf, navigate)
    resp.then(res => {
      if (res.success) {
        setSnackbar({ show: true, msg: res.data?.msg })
        navigate(allIntegURL)
      } else {
        setSnackbar({ show: true, msg: res.data || res })
      }
    })
  }
  const nextPage = pageNo => {
    if (!checkMappedFields(crmConf)) {
      setSnackbar({ show: true, msg: __('Please map mandatory fields', 'bitwpfzc') })
      return
    }

    crmConf.module && crmConf.layout && crmConf.field_map.length > 0 && setstep(pageNo)
  }

  return (
    <div>
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <div className="txt-center w-9 mt-2">
        <Steps step={3} active={step} />
      </div>

      {/* STEP 1 */}
      <ZohoCRMAuthorization
        formID={formID}
        crmConf={crmConf}
        setCrmConf={setCrmConf}
        step={step}
        setstep={setstep}
        isLoading={isLoading}
        setisLoading={setisLoading}
        setSnackbar={setSnackbar}
      />

      {/* STEP 2 */}
      <div
        className="btcd-stp-page"
        style={{ ...(step === 2 && { width: 900, height: `${100}%`, overflow: 'visible' }) }}
      >
        <ZohoCRMIntegLayout
          tab={tab}
          settab={settab}
          formID={formID}
          formFields={formFields}
          handleInput={e => handleInput(e, tab, crmConf, setCrmConf, formID, setisLoading, setSnackbar)}
          crmConf={crmConf}
          setCrmConf={setCrmConf}
          isLoading={isLoading}
          setisLoading={setisLoading}
          setSnackbar={setSnackbar}
        />

        <button
          onClick={() => nextPage(3)}
          disabled={crmConf.module === '' || crmConf.layout === '' || crmConf.field_map.length < 1}
          className="btn f-right btcd-btn-lg green sh-sm flx"
          type="button"
        >
          {__('Next', 'bitwpfzc')} &nbsp;
          <div className="btcd-icn icn-arrow_back rev-icn d-in-b" />
        </button>
      </div>

      {/* STEP 3 */}
      <IntegrationStepThree step={step} saveConfig={() => saveConfig()} />
    </div>
  )
}

export default ZohoCRM
