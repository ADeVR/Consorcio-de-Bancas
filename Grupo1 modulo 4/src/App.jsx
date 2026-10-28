import { useState } from 'react'
import './css/estilos.css'

function App() {

  // =========================
  // ESTADOS PRINCIPALES
  // =========================

  const [modulo, setModulo] = useState('menu')

  const [jugadas, setJugadas] = useState([])

  const [contadorTicket, setContadorTicket] = useState(1)


  // =========================
  // REGISTRAR JUGADA
  // =========================

  const [loteria, setLoteria] = useState('')
  const [numero, setNumero] = useState('')


  const registrarJugada = () => {

    if (loteria === '' || numero === '') {
      alert('Debe completar todos los campos.')
      return
    }

    const nuevaJugada = {
      ticket: contadorTicket,
      loteria: loteria,
      numero: numero,
      estado: 'Registrada'
    }

    setJugadas(prevJugadas => [
      ...prevJugadas,
      nuevaJugada
    ])

    setContadorTicket(prev => prev + 1)

    setLoteria('')
    setNumero('')

    alert('Jugada registrada correctamente.')
  }


  // =========================
  // VOLVER AL MENU
  // =========================

  const volverMenu = () => {
    setModulo('menu')
  }



  // ANULAR JUGADA


  const [jugadaSeleccionada, setJugadaSeleccionada] = useState(0)


  const anularJugada = () => {

    setJugadas(prevJugadas =>
      prevJugadas.map((jugada, index) =>
        index === Number(jugadaSeleccionada)
          ? {
              ...jugada,
              estado: 'Pendiente de Anulación'
            }
          : jugada
      )
    )

    alert(
      'Solicitud de anulación enviada correctamente.'
    )
  }


  // =========================
  // PANTALLA
  // =========================

  return (
    <>

      {/* =========================
          NAVBAR
      ========================= */}

      <nav className="navbar navbar-light bg-white border-bottom">

        <div className="container-fluid">

          <span className="navbar-brand fw-bold text-dark">
            Sistema de Consorcio de Bancas
          </span>

          <span className="text-dark">
            Módulo 4 - Jugar
          </span>

        </div>

      </nav>


      <div className="container mt-5">

        {/* =========================
            TITULO
        ========================= */}

        <h1 className="text-center fw-bold">
          Módulo 4 - Jugar
        </h1>

        <p className="text-center text-muted mb-5">
          Registro y consulta de jugadas
        </p>


        {/* =====================================================
            MENU PRINCIPAL
        ===================================================== */}

        {modulo === 'menu' && (

          <div
            id="menuPrincipal"
            className="row g-4"
          >

            {/* REGISTRAR */}

            <div className="col-md-4">

              <div className="card shadow-sm h-100 text-center">

                <div className="card-body">

                  <div className="fs-1 mb-3">
                    🎟️
                  </div>

                  <h4>
                    Registrar Jugada
                  </h4>

                  <p className="text-muted">
                    Registrar una nueva jugada.
                  </p>

                  <button
                    className="btn btn-secondary"
                    onClick={() => setModulo('registrar')}
                  >
                    Acceder
                  </button>

                </div>

              </div>

            </div>


            {/* CONSULTAR */}

            <div className="col-md-4">

              <div className="card shadow-sm h-100 text-center">

                <div className="card-body">

                  <div className="fs-1 mb-3">
                    🔍
                  </div>

                  <h4>
                    Consultar Jugadas
                  </h4>

                  <p className="text-muted">
                    Consultar las jugadas registradas.
                  </p>

                  <button
                    className="btn btn-secondary"
                    onClick={() => setModulo('consulta')}
                  >
                    Acceder
                  </button>

                </div>

              </div>

            </div>


            {/* ANULACION */}

            <div className="col-md-4">

              <div className="card shadow-sm h-100 text-center">

                <div className="card-body">

                  <div className="fs-1 mb-3">
                    📄
                  </div>

                  <h4>
                    Solicitar Anulación
                  </h4>

                  <p className="text-muted">
                    Gestionar solicitudes de anulación.
                  </p>

                  <button
                    className="btn btn-secondary"
                    onClick={() => setModulo('anulacion')}
                  >
                    Acceder
                  </button>

                </div>

              </div>

            </div>

          </div>

        )}


        {/* =====================================================
            REGISTRAR JUGADA
        ===================================================== */}

        {modulo === 'registrar' && (

          <div
            id="contenidoModulo"
            className="mt-5"
          >

            <div className="card shadow">

              <div className="card-header bg-light border-bottom">

                <h4 className="mb-0 text-dark">
                  Registrar Jugada
                </h4>

              </div>


              <div className="card-body">

                <div className="mb-3">

                  <label className="form-label">
                    Lotería
                  </label>

                  <select
                    className="form-select"
                    value={loteria}
                    onChange={(e) =>
                      setLoteria(e.target.value)
                    }
                  >

                    <option value="">
                      Seleccione una lotería
                    </option>

                    <option value="Loto">
                      Loto
                    </option>

                    <option value="Loteka">
                      Loteka
                    </option>

                    <option value="Leidsa">
                      Leidsa
                    </option>

                    <option value="Nacional">
                      Nacional
                    </option>

                  </select>

                </div>


                <div className="mb-3">

                  <label className="form-label">
                    Número
                  </label>

                  <input
                    type="text"
                    className="form-control"
                    value={numero}
                    onChange={(e) =>
                      setNumero(e.target.value)
                    }
                    placeholder="Ingrese el número"
                  />

                </div>


                <button
                  className="btn btn-secondary"
                  onClick={registrarJugada}
                >
                  Registrar Jugada
                </button>


                <button
                  className="btn btn-secondary ms-2"
                  onClick={volverMenu}
                >
                  Volver al Menú
                </button>

              </div>

            </div>

          </div>

        )}


        {/* =====================================================
            CONSULTA
        ===================================================== */}

        {modulo === 'consulta' && (

          <div
            id="contenidoModulo"
            className="mt-5"
          >

            <div className="card shadow">

              <div className="card-header bg-light border-bottom">

                <h4 className="mb-0 text-dark">
                  Consulta de Jugadas
                </h4>

              </div>


              <div className="card-body">

                {jugadas.length === 0 ? (

                  <>

                    <p>
                      No hay jugadas registradas.
                    </p>

                  </>

                ) : (

                  <div className="table-responsive">

                    <table className="table table-bordered">

                      <thead>

                        <tr>

                          <th>
                            Ticket
                          </th>

                          <th>
                            Lotería
                          </th>

                          <th>
                            Número
                          </th>

                          <th>
                            Estado
                          </th>

                        </tr>

                      </thead>


                      <tbody>

                        {jugadas.map((jugada, index) => (

                          <tr key={index}>

                            <td>
                              {jugada.ticket}
                            </td>

                            <td>
                              {jugada.loteria}
                            </td>

                            <td>
                              {jugada.numero}
                            </td>

                            <td>
                              {jugada.estado}
                            </td>

                          </tr>

                        ))}

                      </tbody>

                    </table>

                  </div>

                )}


                <button
                  className="btn btn-secondary"
                  onClick={volverMenu}
                >
                  Volver al Menú
                </button>

              </div>

            </div>

          </div>

        )}


        {/* =====================================================
            ANULACION
        ===================================================== */}

        {modulo === 'anulacion' && (

          <div
            id="contenidoModulo"
            className="mt-5"
          >

            {jugadas.length === 0 ? (

              <div className="card shadow">

                <div className="card-header bg-light border-bottom">

                  <h4 className="mb-0 text-dark">
                    Solicitar Anulación
                  </h4>

                </div>


                <div className="card-body">

                  <p>
                    No hay jugadas registradas para anular.
                  </p>


                  <button
                    className="btn btn-secondary"
                    onClick={volverMenu}
                  >
                    Volver al Menú
                  </button>

                </div>

              </div>

            ) : (

              <div className="card shadow">

                <div className="card-header bg-light border-bottom">

                  <h4 className="mb-0 text-dark">
                    Solicitar Anulación
                  </h4>

                </div>


                <div className="card-body">

                  <label className="form-label">
                    Seleccione una jugada
                  </label>


                  <select
                    className="form-select"
                    value={jugadaSeleccionada}
                    onChange={(e) =>
                      setJugadaSeleccionada(e.target.value)
                    }
                  >

                    {jugadas.map((jugada, index) => (

                      <option
                        key={index}
                        value={index}
                      >
                        {jugada.ticket} - {jugada.numero}
                      </option>

                    ))}

                  </select>


                  <button
                    className="btn btn-secondary mt-3"
                    onClick={anularJugada}
                  >
                    Solicitar Anulación
                  </button>


                  <button
                    className="btn btn-secondary mt-3 ms-2"
                    onClick={volverMenu}
                  >
                    Volver al Menú
                  </button>

                </div>

              </div>

            )}

          </div>

        )}

      </div>

    </>
  )
}

export default App