import { useState } from 'react'

function Registrar() {
  const [loteria, setLoteria] = useState('')
  const [tipoJugada, setTipoJugada] = useState('')
  const [numeros, setNumeros] = useState('')
  const [monto, setMonto] = useState('')
  const [mensaje, setMensaje] = useState(null)

  const registrarJugada = () => {
    if (loteria === '') {
      setMensaje({
        tipo: 'danger',
        texto: 'Seleccione una lotería.'
      })
      return
    }

    if (tipoJugada === '') {
      setMensaje({
        tipo: 'danger',
        texto: 'Seleccione un tipo de jugada.'
      })
      return
    }

    if (numeros.trim() === '') {
      setMensaje({
        tipo: 'danger',
        texto: 'Introduzca los números.'
      })
      return
    }

    if (monto === '' || Number(monto) <= 0) {
      setMensaje({
        tipo: 'danger',
        texto: 'El monto debe ser mayor que cero.'
      })
      return
    }

    setMensaje({
      tipo: 'success',
      texto: `Jugada registrada correctamente. Número: ${numeros} | Monto: RD$ ${monto}`
    })
  }

  const limpiarFormulario = () => {
    setLoteria('')
    setTipoJugada('')
    setNumeros('')
    setMonto('')
    setMensaje(null)
  }

  return (
    <div className="card shadow">
      <div className="card-header bg-light">
        <h4 className="mb-0">Registrar Jugada</h4>
      </div>

      <div className="card-body">

        <div className="row">

          <div className="col-md-6 mb-3">
            <label className="form-label">Lotería</label>

            <select
              className="form-select"
              value={loteria}
              onChange={(e) => setLoteria(e.target.value)}
            >
              <option value="">Seleccione una lotería</option>
              <option value="Lotería Prueba 1">
                Lotería Prueba 1
              </option>
              <option value="Lotería Prueba 2">
                Lotería Prueba 2
              </option>
            </select>
          </div>

          <div className="col-md-6 mb-3">
            <label className="form-label">Tipo de Jugada</label>

            <select
              className="form-select"
              value={tipoJugada}
              onChange={(e) => setTipoJugada(e.target.value)}
            >
              <option value="">Seleccione un tipo</option>
              <option value="Directa">Directa</option>
              <option value="Pale">Pale</option>
            </select>
          </div>

          <div className="col-md-6 mb-3">
            <label className="form-label">Número(s)</label>

            <input
              type="text"
              className="form-control"
              placeholder="Ejemplo: 25"
              value={numeros}
              onChange={(e) => setNumeros(e.target.value)}
            />
          </div>

          <div className="col-md-6 mb-3">
            <label className="form-label">Monto</label>

            <input
              type="number"
              className="form-control"
              placeholder="100"
              value={monto}
              onChange={(e) => setMonto(e.target.value)}
            />
          </div>

        </div>

        <button
          className="btn btn-secondary"
          onClick={registrarJugada}
        >
          Registrar Jugada
        </button>

        <button
          className="btn btn-secondary ms-2"
          onClick={limpiarFormulario}
        >
          Limpiar
        </button>

        {mensaje && (
          <div className={`alert alert-${mensaje.tipo} mt-3`}>
            {mensaje.texto}
          </div>
        )}

      </div>
    </div>
  )
}

export default Registrar