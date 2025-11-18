// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from '../Utils/i18nwrap'
import greeting from '../resource/img/home.svg'

export default function Welcome({ setModal }) {
  return (
    <div className="btcd-greeting">
      <img src={greeting} alt="" />
      <h2>{__('Welcome to WPForms for Zoho CRM', 'bitwpfzc')}</h2>
      <div className="sub">
        {__('Thank you for installing WPForms for Zoho CRM.', 'bitwpfzc')}
      </div>
      <a href={bitwpfzc?.new_page} className="btn round btcd-btn-lg dp-blue">
        {__('Create First Form', 'bitwpfzc')}
      </a>
    </div>
  )
}
