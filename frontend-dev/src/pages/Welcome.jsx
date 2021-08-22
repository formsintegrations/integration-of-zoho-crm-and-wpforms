// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from '../Utils/i18nwrap'
import greeting from '../resource/img/home.svg'

export default function Welcome({ setModal }) {
  return (
    <div className="btcd-greeting">
      <img src={greeting} alt="" />
      <h2>{__('Welcome to Zoho CRM for wpforms', 'bitwpfzc')}</h2>
      <div className="sub">
        {__('Thank you for installing Zoho CRM for wpforms.', 'bitwpfzc')}
      </div>
      <div>
        {__('Modern Form builder and database management  system', 'bitwpfzc')}
        <br />
        {__('for Wordpress', 'bitwpfzc')}
      </div>
      <button onClick={() => setModal(true)} type="button" className="btn round btcd-btn-lg dp-blue">{__('Create First Form', 'bitwpfzc')}</button>
    </div>
  )
}
