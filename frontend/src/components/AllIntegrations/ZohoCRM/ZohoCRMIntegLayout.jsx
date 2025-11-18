import { Fragment } from 'react'
import { Panel, Tab, Tabs } from '@bumaga/tabs'
import { __ } from '../../../Utils/i18nwrap'
import ZohoCRMNewRecord from './ZohoCRMNewRecord'
import ZohoCRMRelatedRecord from './ZohoCRMRelatedRecord'
import { refreshModules, handleTabChange } from './ZohoCRMCommonFunc'
import CloseIcn from '../../../Icons/CloseIcn'

export default function ZohoCRMIntegLayout({
  tab,
  settab,
  formID,
  formFields,
  handleInput,
  crmConf,
  setCrmConf,
  isLoading,
  setisLoading,
  setSnackbar
}) {
  const addNewRelatedTab = () => {
    if (crmConf.relatedlists.length < 3) {
      const newConf = { ...crmConf }
      newConf.relatedlists.push({
        actions: {},
        field_map: [{ formField: '', zohoFormField: '' }],
        layout: '',
        module: '',
        upload_field_map: [{ formField: '', zohoFormField: '' }]
      })
      setCrmConf({ ...newConf })
    }
  }

  const removeRelatedTab = indx => {
    const newConf = { ...crmConf }

    newConf.relatedlists.splice(indx, 1)

    if (!newConf.relatedlists.length) settab(0)

    setCrmConf({ ...newConf })
  }

  return (
    <>
      <br />
      <b className="wdt-100 d-in-b">{__('Module:', 'bitwpfzc')}</b>
      <select
        onChange={handleInput}
        name="module"
        value={crmConf.module}
        className="btcd-paper-inp w-7"
        disabled={tab}
      >
        <option value="">{__('Select Module', 'bitwpfzc')}</option>
        {crmConf?.default?.modules &&
          Object.keys(crmConf.default.modules).map(moduleApiName => (
            <option key={moduleApiName} value={moduleApiName}>
              {crmConf.default.modules[moduleApiName].plural_label}
            </option>
          ))}
        <option value="" disabled>
          {__('More module on the Pro version', 'bitwpfzc')}
        </option>
      </select>
      {tab === 0 && (
        <button
          onClick={() => refreshModules(formID, crmConf, setCrmConf, setisLoading, setSnackbar)}
          className="icn-btn sh-sm ml-2 mr-2 tooltip"
          style={{ '--tooltip-txt': `'${__('Refresh CRM Modules', 'bitwpfzc')}'` }}
          type="button"
          disabled={isLoading}
        >
          &#x21BB;
        </button>
      )}
      <br />
      <div>
        <Tabs>
          <div className="flx mt-2">
            <Tab>
              <button
                onClick={() => settab(0)}
                className={`btcd-s-tab-link ${tab === 0 && 's-t-l-active'}`}
                type="button"
              >
                {__('New Record', 'bitwpfzc')}
              </button>
            </Tab>

            {crmConf?.relatedlists &&
              crmConf.relatedlists.map((_, indx) => (
                <>
                  <Tab key={`t-${indx * 3}`}>
                    <button
                      onClick={() =>
                        handleTabChange(
                          indx + 1,
                          settab,
                          formID,
                          crmConf,
                          setCrmConf,
                          setisLoading,
                          setSnackbar
                        )
                      }
                      className={`btcd-s-tab-link ${tab === indx + 1 && 's-t-l-active'}`}
                      type="button"
                    >
                      {__('Related List #', 'bitwpfzc')}
                      {indx + 1}
                    </button>
                  </Tab>
                  <button
                    onClick={() => removeRelatedTab(indx)}
                    className="icn-btn"
                    aria-label="delete-relatedlist"
                    type="button"
                  >
                    <CloseIcn size="14" />
                  </button>
                </>
              ))}
            {crmConf.relatedlists.length < 3 && (
              <button
                onClick={addNewRelatedTab}
                className="icn-btn sh-sm ml-2 mr-2 tooltip"
                style={{ '--tooltip-txt': `'${__('Add More Related List', 'bitwpfzc')}'` }}
                type="button"
              >
                +
              </button>
            )}
          </div>
          <div className="btcd-hr" />

          <Panel>
            <ZohoCRMNewRecord
              tab={tab}
              settab={settab}
              formID={formID}
              formFields={formFields}
              crmConf={crmConf}
              setCrmConf={setCrmConf}
              handleInput={handleInput}
              isLoading={isLoading}
              setisLoading={setisLoading}
              setSnackbar={setSnackbar}
            />
          </Panel>
          {crmConf?.relatedlists &&
            crmConf.relatedlists.map((_, indx) => (
              <Panel key={`p-${indx + 2.4}`}>
                <ZohoCRMRelatedRecord
                  indx={indx}
                  tab={tab}
                  settab={settab}
                  formID={formID}
                  formFields={formFields}
                  crmConf={crmConf}
                  setCrmConf={setCrmConf}
                  handleInput={handleInput}
                  isLoading={isLoading}
                  setisLoading={setisLoading}
                  setSnackbar={setSnackbar}
                />
              </Panel>
            ))}
        </Tabs>
      </div>
      <br />
    </>
  )
}
