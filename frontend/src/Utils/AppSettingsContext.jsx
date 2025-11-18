/* eslint-disable no-undef */
import { createContext, useState } from 'react'

export const AppSettings = createContext()

export default function AppSettingsProvider({ children }) {
  const [reCaptchaV2, setreCaptchaV2] = useState(
    // eslint-disable-next-line no-undef
    bitwpfzc?.allFormSettings?.gReCaptcha ? bitwpfzc.allFormSettings.gReCaptcha
      : {
        siteKey: '',
        secretKey: '',
      },
  )
  const [reCaptchaV3, setreCaptchaV3] = useState(
    // eslint-disable-next-line no-undef
    bitwpfzc?.allFormSettings?.gReCaptchaV3 ? bitwpfzc.allFormSettings.gReCaptchaV3
      : {
        siteKey: '',
        secretKey: '',
      },
  )

  const paymentsState = () => {
    if (bitwpfzc?.allFormSettings?.payments) {
      const pays = bitwpfzc.allFormSettings.payments
      if (Array.isArray(pays)) return pays
      return [pays]
    }
    return []
  }

  const [payments, setPayments] = useState(paymentsState())
  return (
    <AppSettings.Provider value={{ reCaptchaV2, setreCaptchaV2, reCaptchaV3, setreCaptchaV3, payments, setPayments }}>
      {children}
    </AppSettings.Provider>
  )
}
