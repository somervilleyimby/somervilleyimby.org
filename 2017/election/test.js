function toggle (className, obj) {
  var $input = $(obj)
  if ($input.prop('checked')) {
    $(className).hide()
  } else {
    $(className).show()
  }
}
