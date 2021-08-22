/* eslint-disable jsx-a11y/label-has-associated-control */
export default function SingleToggle2({ className, title, action, disabled, checked, tooltip }) {
  const onChange = (e) => {
    if (!disabled) {
      action(e)
    }
  }
  return (
    <div className={`${className}`}>
      <span>{title}</span>
      <label className={`btcd-label ${tooltip && 'tooltip'}`} style={tooltip}>
        <div className="btcd-toggle">
          <input aria-label={title} onChange={onChange} className="btcd-toggle-state" type="checkbox" name="check" value="check" checked={checked} />
          <div className="btcd-toggle-inner">
            <div className="btcd-indicator" />
          </div>
          <div className="btcd-active-bg" />
        </div>
        <div className="btcd-label-text" />
      </label>
    </div>
  )
}
