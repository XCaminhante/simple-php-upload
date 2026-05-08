function e(id) { return document.getElementById(id) }
var target_form = e('simpleupload-form')
var target_ul = e('simpleupload-ul')
var target_input = e('simpleupload-input')
var settings_listfiles = true
function init () {
  target_form.addEventListener('dragover', function (event) { event.preventDefault() }, false)
  target_form.addEventListener('drop', handleFiles, false)
  target_input.onchange = function () {
    addFileLi('Uploading...', '')
    target_form.submit()
  }
}

function addFileLi (name, info) {
  if (settings_listfiles == false) { return }
  target_form.style.display = 'none'
  var new_li = document.createElement('li')
  new_li.className = 'uploading'
  var new_a = document.createElement('a')
  new_a.innerHTML = name
  new_li.appendChild(new_a)
  var new_span = document.createElement('span')
  new_span.innerHTML = info
  new_a.appendChild(new_span)
  target_ul.insertBefore(new_li, target_ul.firstChild)
}

function handleFiles (event) {
  event.preventDefault()
  var files = event.dataTransfer.files
  var form = new FormData()
  for (var i = 0; i < files.length; i++) {
    form.append('file[]', files[i])
    addFileLi('Uploading '+files[i].name+' ...', i)
  }
  var xhr = new XMLHttpRequest()
  xhr.onload = function() { window.location.assign(location.href) }
  xhr.open('post', location.href, true)
  xhr.send(form)
}

init()
