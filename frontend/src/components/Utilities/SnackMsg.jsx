import { useRef } from 'react'
import { CSSTransition } from 'react-transition-group'

function SnackMsg({ snack, setSnackbar }) {
  const { show, msg } = snack
  const nodeRef = useRef(null)

  return (
    <CSSTransition
      nodeRef={nodeRef}
      in={show}
      timeout={3000}
      classNames="flx btcd-snack btcd-snack-a"
      onEntered={() =>
        setTimeout(() => {
          setSnackbar({ show: false, msg })
        }, 1)
      }
      unmountOnExit
    >
      <div ref={nodeRef}>
        <span
          // eslint-disable-next-line react/no-danger
          dangerouslySetInnerHTML={{ __html: msg }}
        />
        <button
          onClick={() => setSnackbar({ show: false, msg })}
          className="btcd-snack-cls"
          type="button"
        >
          &times;
        </button>
      </div>
    </CSSTransition>
  )
}

export default SnackMsg
