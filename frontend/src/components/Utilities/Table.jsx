/* eslint-disable no-nested-ternary */
/* eslint-disable react/jsx-props-no-spreading */

import { memo, useContext, useEffect, useState, useRef, forwardRef } from 'react'
import { Scrollbars } from 'react-custom-scrollbars'
import { ReactSortable } from 'react-sortablejs'
import { useColumnOrder, useFilters, useFlexLayout, useGlobalFilter, usePagination, useResizeColumns, useRowSelect, useSortBy, useTable } from 'react-table'
import { useSticky } from 'react-table-sticky'
import { __ } from '../../Utils/i18nwrap'
import { AllFormContext } from '../../Utils/AllFormContext'
import ConfirmModal from './ConfirmModal'
import Menu from './Menu'
import TableCheckBox from './TableCheckBox'
import TableLoader2 from '../Loaders/TableLoader2'

const IndeterminateCheckbox = forwardRef(
  ({ indeterminate, ...rest }, ref) => {
    const defaultRef = useRef()
    const resolvedRef = ref || defaultRef
    useEffect(() => {
      resolvedRef.current.indeterminate = indeterminate
    }, [resolvedRef, indeterminate])
    return <TableCheckBox refer={resolvedRef} rest={rest} />
  },
)

function GlobalFilter({ globalFilter, setGlobalFilter, setSearch, exportImportMenu, data, cols, formID, report }) {
  const [delay, setDelay] = useState(null)
  const handleSearch = e => {
    delay && clearTimeout(delay)
    const { value } = e.target

    setGlobalFilter(value || undefined)

    setDelay(setTimeout(() => {
      setSearch(value || undefined)
    }, 1000))
  }

  return (
    <div className="f-search">
      <button type="button" className="icn-btn" aria-label="icon-btn" onClick={() => { setSearch(globalFilter || undefined) }}><span className="btcd-icn icn-search" /></button>
      {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
      <label>
        <input
          value={globalFilter || ''}
          onChange={handleSearch}
          placeholder={__('Search', 'bitwpfzc')}
        />
      </label>
    </div>
  )
}

function ColumnHide({ cols, setCols, tableCol, tableAllCols }) {
  return (
    <Menu icn="icn-remove_red_eye">
      <Scrollbars autoHide style={{ width: 200 }}>
        <ReactSortable list={cols} setList={l => setCols(l)} handle=".btcd-pane-drg">
          {tableCol.map((column, i) => (
            <div key={tableAllCols[i + 1].id} className={`btcd-pane ${(column.Header === 'Actions' || typeof column.Header === 'object') && 'd-non'}`}>
              <TableCheckBox cls="scl-7" id={tableAllCols[i + 1].id} title={column.Header} rest={tableAllCols[i + 1].getToggleHiddenProps()} />
              <span className="btcd-pane-drg">&#8759;</span>
            </div>
          ))}
        </ReactSortable>
      </Scrollbars>
    </Menu>
  )
}

function Table(props) {
  console.log('%c $render Table', 'background:blue;padding:3px;border-radius:5px;color:white')
  const [confMdl, setconfMdl] = useState({ show: false, btnTxt: '' })
  const { columns, data, fetchData, report } = props
  const { getTableProps,
    getTableBodyProps,
    headerGroups,
    prepareRow,
    page,
    canPreviousPage,
    canNextPage,
    pageOptions,
    pageCount,
    gotoPage,
    nextPage,
    previousPage,
    setPageSize,
    state,
    preGlobalFilteredRows,
    selectedFlatRows, // row select
    allColumns, // col hide
    setGlobalFilter,
    state: { pageIndex, pageSize, sortBy, filters, globalFilter, hiddenColumns },
    setColumnOrder,
    setHiddenColumns } = useTable(
    {
      debug: true,
      fetchData,
      columns,
      data,
      manualPagination: typeof props.pageCount !== 'undefined',
      pageCount: props.pageCount,
      autoResetPage: false,
      autoResetHiddenColumns: false,
      autoResetSortBy: false,
      autoResetFilters: false,
      autoResetGlobalFilter: false,
    },
    useFilters,
    useGlobalFilter,
    useSortBy,
    usePagination,
    useSticky,
    useColumnOrder,
    // useBlockLayout,
    useFlexLayout,
    props.resizable ? useResizeColumns : '', // resize
    props.rowSeletable ? useRowSelect : '', // row select
    props.rowSeletable ? (hooks => {
      hooks.allColumns.push(cols => [
        {
          id: 'selection',
          width: 50,
          maxWidth: 50,
          minWidth: 67,
          sticky: 'left',
          Header: ({ getToggleAllRowsSelectedProps }) => <IndeterminateCheckbox {...getToggleAllRowsSelectedProps()} />,
          Cell: ({ row }) => <IndeterminateCheckbox {...row.getToggleRowSelectedProps()} />,
        },
        ...cols,
      ])
    }) : '',
  )
  const [reportID, setreportID] = useState(parseInt(report, 10))
  const [stateSavable, setstateSavable] = useState(false)
  const [search, setSearch] = useState(globalFilter)
  useEffect(() => {
    if (fetchData) {
      fetchData({ pageIndex, pageSize })
    }
  }, [fetchData, pageIndex, pageSize])
  useEffect(() => {
    if (pageIndex > pageCount) {
      gotoPage(0)
    }
  }, [gotoPage, pageCount, pageIndex])

  // useEffect(() => {
  //   if (!isNaN(reportID) && reports.length > 0 && reports[reportID] && 'details' in reports[reportID]) {
  //     let details
  //     if (typeof reports[reportID].details === 'object' && reports[reportID].details) {
  //       details = { ...reports[reportID].details, hiddenColumns, pageSize, sortBy, filters, globalFilter }
  //     } else {
  //       details = { hiddenColumns, pageSize, sortBy, filters, globalFilter }
  //     }
  //     reportsDispatch({ type: 'update', report: { ...reports[reportID], details, type: 'table' }, reportID })
  //     setstateSavable(false)
  //   } else if (stateSavable) {
  //     setstateSavable(false)
  //   }
  //   // eslint-disable-next-line react-hooks/exhaustive-deps
  // }, [pageSize, sortBy, filters, globalFilter, hiddenColumns])

  // useEffect(() => {
  //   // setReport if not initially setted
  //   if (typeof props.pageCount !== 'undefined' && state.columnOrder.length === 0 && !isNaN(reportID) && reports.length > 0 && reports[reportID] !== null && typeof reports[reportID].details === 'object' && reports[reportID].details) {
  //     if (!stateSavable) {
  //       if ('hiddenColumns' in reports[reportID].details && reports[reportID].details.hiddenColumns !== state.hiddenColumns) {
  //         setHiddenColumns(reports[reportID].details.hiddenColumns)
  //       }
  //       if ('pageSize' in reports[reportID].details && reports[reportID].details.pageSize !== pageSize) {
  //         setPageSize(reports[reportID].details.pageSize)
  //       }
  //       if ('pageIndex' in reports[reportID].details && reports[reportID].details.pageIndex !== pageIndex) {
  //         gotoPage(reports[reportID].details.pageIndex - 1)
  //       }
  //       if ('sortBy' in reports[reportID].details && reports[reportID].details.sortBy !== state.sortBy && data !== null) {
  //         // reports[reportID].details.sortBy.map(fieldToSort => { console.log({...fieldToSort}); toggleSortBy(fieldToSort.id, fieldToSort.desc) })
  //       }
  //       // if ('filters' in reports[reportID].details && reports[reportID].details.filters !== state.filters) {}
  //       if ('globalFilter' in reports[reportID].details && reports[reportID].details.globalFilter !== state.globalFilter) {
  //         setGlobalFilter(reports[reportID].details.globalFilter)
  //         setSearch(reports[reportID].details.globalFilter)
  //       }
  //       if ('order' in reports[reportID].details) {
  //         setColumnOrder(reports[reportID].details.order)
  //       }
  //     }
  //   }
  //   // eslint-disable-next-line react-hooks/exhaustive-deps
  // }, [reports[report]])

  // useEffect(() => {
  //   if (columns.length > 0 && allColumns.length >= columns.length) {
  //     if (!isNaN(reportID) && reports.length > 0 && reports[reportID] && 'details' in reports[reportID]) {
  //       if (stateSavable && reports[reportID].details) {
  //         let details
  //         if (typeof reports[reportID].details === 'object' && 'order' in reports[reportID].details) {
  //           details = { ...reports[reportID].details, order: ['selection', ...columns.map(singleColumn => ('id' in singleColumn ? singleColumn.id : singleColumn.accessor))], type: 'table' }
  //         } else {
  //           details = { ...reports[reportID].details, order: ['selection', ...columns.map(singleColumn => ('id' in singleColumn ? singleColumn.id : singleColumn.accessor))], type: 'table' }
  //         }
  //         const newReport = { ...reports[reportID], details, type: 'table' }
  //         if (state.columnOrder.length === 0 && typeof reports[reportID].details === 'object' && 'order' in reports[reportID].details) {
  //           // const actionColumn = columns[columns.length - 1] // table action column
  //           // props.setTableCols(reports[reportID].details.order.map(singleColumn => ('id' in singleColumn && singleColumn.id === 't_action' ? actionColumn : singleColumn)))
  //           setColumnOrder(reports[reportID].details.order)
  //         } else {
  //           setColumnOrder(details.order)
  //           reportsDispatch({ type: 'update', report: newReport, reportID })
  //         }
  //       } else if (!stateSavable && typeof reports[reportID].details === 'object' && reports[reportID].details && 'order' in reports[reportID].details) {
  //         // const actionColumn = columns[columns.length - 1] // table action column
  //         // props.setTableCols(reports[reportID].details.order.map(singleColumn => ('id' in singleColumn && singleColumn.id === 't_action' ? actionColumn : singleColumn)))
  //         setColumnOrder(reports[reportID].details.order)
  //         setstateSavable(true)
  //       } else if (!stateSavable/*  && typeof reports[reportID].details !== 'object' */) {
  //         setstateSavable(true)
  //       }
  //     } else if (typeof props.pageCount !== 'undefined' && (isNaN(reportID) || reports.length === 0)) {
  //       const details = { hiddenColumns: state.hiddenColumns, order: ['selection', ...columns.map(singleColumn => ('id' in singleColumn ? singleColumn.id : singleColumn.accessor))], pageSize, sortBy: state.sortBy, filters: state.filters, globalFilter: state.globalFilter }
  //       setreportID(reports.length)
  //       reportsDispatch({ type: 'add', report: { details, type: 'table' } })
  //     }
  //   }
  //   // eslint-disable-next-line react-hooks/exhaustive-deps
  // }, [columns])

  const showBulkDupMdl = () => {
    confMdl.action = () => { props.duplicateData(selectedFlatRows, data, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter: search } }); closeConfMdl() }
    confMdl.btnTxt = __('Duplicate', 'bitwpfzc')
    confMdl.btn2Txt = null
    confMdl.btnClass = 'blue'
    confMdl.body = `${__('Do You want Deplicate these', 'bitwpfzc')} ${selectedFlatRows.length} ${__('item', 'bitwpfzc')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const showStModal = () => {
    confMdl.action = (e) => { props.setBulkStatus(e, selectedFlatRows); closeConfMdl() }
    confMdl.btn2Action = (e) => { props.setBulkStatus(e, selectedFlatRows); closeConfMdl() }
    confMdl.btnTxt = __('Disable', 'bitwpfzc')
    confMdl.btn2Txt = __('Enable', 'bitwpfzc')
    confMdl.body = `${__('Do you want to change these', 'bitwpfzc')} ${selectedFlatRows.length} ${__('status', 'bitwpfzc')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const showDelModal = () => {
    confMdl.action = () => { props.setBulkDelete(selectedFlatRows, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter: search } }); closeConfMdl() }
    confMdl.btnTxt = __('Delete', 'bitwpfzc')
    confMdl.btn2Txt = null
    confMdl.btnClass = ''
    confMdl.body = `${__('Are you sure to delete these', 'bitwpfzc')} ${selectedFlatRows.length} ${__('items', 'bitwpfzc')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const closeConfMdl = () => {
    confMdl.show = false
    setconfMdl({ ...confMdl })
  }

  return (
    <>
      <ConfirmModal
        show={confMdl.show}
        body={confMdl.body}
        action={confMdl.action}
        close={closeConfMdl}
        btnTxt={confMdl.btnTxt}
        btn2Txt={confMdl.btn2Txt}
        btn2Action={confMdl.btn2Action}
        btnClass={confMdl.btnClass}
      />
      <div className="btcd-t-actions">
        <div className="flx">

          {props.columnHidable && <ColumnHide cols={props.columns} setCols={props.setTableCols} tableCol={columns} tableAllCols={allColumns} />}

          {props.rowSeletable && selectedFlatRows.length > 0
            && (
              <>
                {'setBulkStatus' in props
                  && (
                    <button onClick={showStModal} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Status"' }} aria-label="icon-btn" type="button">
                      <span className="btcd-icn icn-toggle_off" />
                    </button>
                  )}
                {'duplicateData' in props
                  && (
                    <button onClick={showBulkDupMdl} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Duplicate"' }} aria-label="icon-btn" type="button">
                      <span className="btcd-icn icn-file_copy" style={{ fontSize: 16 }} />
                    </button>
                  )}
                <button onClick={showDelModal} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Delete"' }} aria-label="icon-btn" type="button">
                  <span className="btcd-icn icn-trash-fill" style={{ fontSize: 16 }} />
                </button>
                <small className="btcd-pill">
                  {selectedFlatRows.length}
                  {' '}
                  {__('Row Selected', 'bitwpfzc')}
                </small>
              </>
            )}
        </div>
      </div>
      <>
        {props.search &&
          <GlobalFilter
          preGlobalFilteredRows={preGlobalFilteredRows}
          globalFilter={state.globalFilter}
          setGlobalFilter={setGlobalFilter}
          setSearch={setSearch}
          exportImportMenu={props.exportImportMenu}
          data={props.data}
          cols={props.columns}
          formID={props.formID}
          report={report}
          />}
        <div className="mt-2">
          <Scrollbars className="btcd-scroll" style={{ height: props.height }}>
            <div {...getTableProps()} className={`${props.className} ${props.rowClickable && 'rowClickable'}`}>
              <div className="thead">
                {headerGroups.map((headerGroup, i) => (
                  <div key={`t-th-${i + 8}`} className="tr" {...headerGroup.getHeaderGroupProps()}>
                    {headerGroup.headers.map(column => (
                      <div key={column.id} className="th flx" {...column.getHeaderProps()}>
                        <div {...column.id !== 't_action' && column.getSortByToggleProps()}>
                          {column.render('Header')}
                          {' '}
                          {(column.id !== 't_action' && column.id !== 'selection') && (
                            <span>
                              {column.isSorted
                                ? column.isSortedDesc
                                  ? String.fromCharCode(9662)
                                  : String.fromCharCode(9652)
                                : <span className="btcd-icn icn-sort" style={{ fontSize: 10, marginLeft: 5 }} />}
                            </span>
                          )}
                        </div>
                        {props.resizable
                          && (
                            <div
                              {...column.getResizerProps()}
                              className={`btcd-t-resizer ${column.isResizing ? 'isResizing' : ''}`}
                            />
                          )}
                      </div>
                    ))}
                  </div>
                ))}
              </div>
              {props.loading ? <TableLoader2 /> : (
                <div className="tbody" {...getTableBodyProps()}>
                  {page.map(row => {
                    prepareRow(row)
                    return (
                      <div
                        key={`t-r-${row.index}`}
                        className={`tr ${row.isSelected ? 'btcd-row-selected' : ''}`}
                        {...row.getRowProps()}
                      >
                        {row.cells.map(cell => (
                          <div
                            key={`t-d-${cell.row.index}`}
                            className="td flx"
                            {...cell.getCellProps()}
                            onClick={(e) => props.rowClickable && typeof cell.column.Header === 'string' && props.onRowClick(e, row.cells, cell.row.index, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter } })}
                            onKeyPress={(e) => props.rowClickable && typeof cell.column.Header === 'string' && props.onRowClick(e, row.cells, cell.row.index, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter } })}
                            role="button"
                            tabIndex={0}
                            aria-label="cell"
                          >
                            {cell.render('Cell')}
                          </div>
                        ))}
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          </Scrollbars>
        </div>
      </>

      <div className="btcd-pagination">
        <small>
          {props.countEntries >= 0 && (
            `${__('Total Response:', 'bitwpfzc')}
            ${props.countEntries}`
          )}
        </small>
        <div>
          <button aria-label="Go first" className="icn-btn" type="button" onClick={() => gotoPage(0)} disabled={!canPreviousPage}>
            &laquo;
          </button>
          {' '}
          <button aria-label="Back" className="icn-btn" type="button" onClick={() => previousPage()} disabled={!canPreviousPage}>
            &lsaquo;
          </button>
          {' '}
          <button aria-label="Next" className="icn-btn" type="button" onClick={() => nextPage()} disabled={!canNextPage}>
            &rsaquo;
          </button>
          {' '}
          <button aria-label="Last" className="icn-btn" type="button" onClick={() => gotoPage(pageCount - 1)} disabled={!canNextPage}>
            &raquo;
          </button>
          {' '}
          <small>
            &nbsp;
            {__('Page', 'bitwpfzc')}
            {' '}
            <strong>
              {pageIndex + 1}
              {' '}
              {__('of', 'bitwpfzc')}
              {' '}
              {pageOptions.length}
              {' '}
            &nbsp;
            </strong>
            {' '}
          </small>
          {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
          <label>
            <select
              className="btcd-paper-inp"
              value={pageSize}
              onChange={e => {
                setPageSize(Number(e.target.value))
                if (props.getPageSize) {
                  props.getPageSize(e.target.value, pageIndex)
                }
              }}
            >
              {[10, 20, 30, 40, 50].map(pageSiz => (
                <option key={pageSiz} value={pageSiz}>
                  {__('Show', 'bitwpfzc')}
                  {' '}
                  {pageSiz}
                </option>
              ))}
            </select>
          </label>
        </div>
      </div>

    </>
  )
}

export default memo(Table)
