/* Solo UX: oculta elementos con data-requiere-permiso. La seguridad real la aplica el servidor en cada endpoint. */
const Permisos = (() => {
  const lista = String($('body').data('permisos') || '').split(',').filter(Boolean);
  const puede = (permiso) => lista.includes(permiso);

  $(() => {
    $('[data-requiere-permiso]').each(function () {
      if (!puede($(this).attr('data-requiere-permiso'))) $(this).addClass('d-none');
    });
  });

  return { puede };
})();
