function Consultar({ jugadas, volverMenu }) {
  return (
    <div className="card shadow">
      <div className="card-header bg-light border-bottom">
        <h4 className="mb-0 text-dark">
          Consultar Jugadas
        </h4>
      </div>

      <div className="card-body">

        <table className="table table-striped">

          <thead>
            <tr>
              <th>Ticket</th>
              <th>Lotería</th>
              <th>Tipo</th>
              <th>Número</th>
              <th>Monto</th>
              <th>Estado</th>
            </tr>
          </thead>

          <tbody>

            {jugadas.length === 0 ? (
              <tr>
                <td colSpan="6" className="text-center">
                  No hay jugadas registradas.
                </td>
              </tr>
            ) : (
              jugadas.map((jugada) => (
                <tr key={jugada.ticket}>
                  <td>{jugada.ticket}</td>
                  <td>{jugada.loteria}</td>
                  <td>{jugada.tipo}</td>
                  <td>{jugada.numero}</td>
                  <td>RD$ {jugada.monto}</td>
                  <td>{jugada.estado}</td>
                </tr>
              ))
            )}

          </tbody>

        </table>

        <button
          className="btn btn-secondary"
          onClick={volverMenu}
        >
          Volver al Menú
        </button>

      </div>
    </div>
  )
}

export default Consultar