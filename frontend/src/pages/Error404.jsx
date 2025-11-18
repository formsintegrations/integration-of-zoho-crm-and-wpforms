import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { __ } from '../Utils/i18nwrap'
import space from '../resource/img/space.svg'

export default function Error404() {
  const [sec, setsec] = useState(9)
  const navigate = useNavigate()
  useEffect(() => {
    setTimeout(() => {
      if (sec === 0) {
        navigate('/')
      }
      setsec(sec - 1)
    }, 1000)
  }, [navigate, sec])

  return (
    <div className="error-404">
      <div>
        <div className="four">{__('404', 'bitwpfzc')}</div>
        <div className="t">{__('Lost In Space', 'bitwpfzc')}</div>
        <br />
        {__('Redirecting Home in', 'bitwpfzc')}
        {' '}
        {sec}
        <br />
        <br />
        <Link to="/" className="btn dp-blue btcd-btn-lg">{__('Go Home', 'bitwpfzc')}</Link>
      </div>
      <img src={space} alt="404 not found" />
    </div>
  )
}
