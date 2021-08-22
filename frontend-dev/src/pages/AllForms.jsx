/* eslint-disable no-undef */
// eslint-disable-next-line import/no-extraneous-dependencies
import { lazy, memo, useCallback, useContext, useState } from 'react'
import { Link } from 'react-router-dom'
import { __ } from '../Utils/i18nwrap'
import CopyText from '../components/Utilities/CopyText'
import SingleToggle2 from '../components/Utilities/SingleToggle2'
import SnackMsg from '../components/Utilities/SnackMsg'
import Table from '../components/Utilities/Table'
import { AllFormContext } from '../Utils/AllFormContext'
import bitsFetch from '../Utils/bitsFetch'

const Welcome = lazy(() => import('./Welcome'))

function AllFroms({ newFormId }) {
  const [modal, setModal] = useState(false)
  const [snack, setSnackbar] = useState({ show: false })
  const { allFormsData } = useContext(AllFormContext)
  const { allForms, allFormsDispatchHandler } = allFormsData
  const [confMdl, setconfMdl] = useState({ show: false, btnTxt: '' })
  const handleStatus = (e, id) => {
    const status = e.target.checked
    const data = { id, status }
    console.log('status', status)
    let action = 'gclid/disable'
    if (status) {
      action = 'gclid/enable'
    }
    allFormsDispatchHandler({ type: 'update', data: { formID: id, status: data.status } })
    bitsFetch(data, action)
      .then(res => {
        if ('success' in res && !res.success) {
          allFormsDispatchHandler({ type: 'update', data: { formID: id, status: !data.status } })
        }
        setSnackbar({ ...{ show: true, msg: res.data } })
      }).catch(() => {
        allFormsDispatchHandler({ type: 'update', data: { formID: id, status: !status } })
        setSnackbar({ ...{ show: true, msg: __('Failed to enable gclid', 'bitwpfzc') } })
      })
  }
  const [cols, setCols] = useState([
    { width: 250, minWidth: 80, Header: __('Form Name', 'bitwpfzc'), accessor: 'formName', Cell: v => <Link to={`/form/${v.row.original.formID}/integrations`} className="btcd-tabl-lnk">{v.row.values.formName}</Link> },
    { width: 220, minWidth: 200, Header: __('Short Code', 'bitwpfzc'), accessor: 'shortcode', Cell: val => <CopyText value={`[${val.row.values.shortcode}]`} setSnackbar={setSnackbar} className="cpyTxt" /> },
    { width: 70, minWidth: 60, Header: __('Gclid', 'bitwpfzc'), accessor: 'status', Cell: value => <SingleToggle2 className="flx" disabled checked={value.row.original.status} tooltip={{ '--tooltip-txt': '"Available in Pro"', '--tt-left': '85%' }} /> },
 ])

  const setTableCols = useCallback(newCols => { setCols(newCols) }, [])

  return (
    <div id="all-forms">
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      {allForms.length ? (
        <>
          <div className="forms">
            <Table
              className="f-table btcd-all-frm"
              height={500}
              columns={cols}
              data={allForms}
              rowSeletable
              newFormId={newFormId}
              resizable
              columnHidable
              setTableCols={setTableCols}
              search
            />
          </div>
        </>
      ) : <Welcome setModal={setModal} />}
    </div>
  )
}

export default memo(AllFroms)
