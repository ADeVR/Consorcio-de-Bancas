function Anulacion({ jugadas, setJugadas, volverMenu }) {
  const [jugadaSeleccionada, setJugadaSeleccionada] = useState(0);

  const anularJugada = () => {
    setJugadas(prevJugadas =>
      prevJugadas.map((jugada, index) =>
        index === Number(jugadaSeleccionada)
          ? {
              ...jugada,
              estado: "Pendiente de Anulación"
            }
          : jugada
      )
    );

    alert("Solicitud de anulación enviada correctamente.");
  };

  if (jugadas.length === 0) {
    return (
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
    );
  }

  return (
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
            <option key={index} value={index}>
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
  );
}