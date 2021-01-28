var SimpldeMDE = require('simplemde')
const $ = require('jquery');
import { decode } from 'js-base64';

if (location.pathname === '/admin/edit') {
  let mde = new SimpldeMDE({
    autofocus: true,
    forceSync: true,
  })

  mde.codemirror.on('change', function () {
    let changeDiff = new Date() - lastChange
    window.clearInterval(saver)
    saver = window.setTimeout(save, 5000)
    lastChange = new Date()
  })

  document
    .querySelector('.CodeMirror')
    .addEventListener('keydown', function (event) {
      if (event.code === 'KeyS' && event.ctrlKey) {
        event.preventDefault()
        save()
      }
    })

  window.mde = mde
}

let saver = null
let lastChange = new Date()

function addMeta(name) {
  $('#meta-tags').append(
    '<div class="row"><label>' +
    name +
    ': <input type="text" name="meta[' +
    name +
    ']"></div>',
  )
  $('#new-meta-tag input').val('')
}

function deleteMetaTag(name) {
  if (!confirm('Are you sure you want to delete the tag ' + name + '?')) {
    return null
  }
  $('#' + name).remove()
  save()
}

function save() {
  window.clearInterval(saver)
  let xhr = new XMLHttpRequest()
  xhr.open('POST', '/admin/edit')
  let form = new FormData(document.forms[0])
  xhr.send(form)
  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return
    }
    document.getElementById(
      'last-saved',
    ).innerText = new Date().toLocaleTimeString()
  }
}

if (!referer.includes(location.hostname + '/admin')) {
  localStorage.setItem('referer', referer)
}

if (localStorage.getItem('referer') !== null) {
  document
    .getElementById('referer')
    .setAttribute('href', localStorage.getItem('referer'))
} else {
  document.getElementById('referer').setAttribute('href', location.hostname)
}

function toggleNav() {
  $('#page-menu ul').slideToggle()
  window.setTimeout(function () {
    let display = $('#page-menu ul').css('display') === 'block'
    localStorage.setItem('showPageMenu', display)
  }, 1000)
}

$(function () {
  let display = localStorage.getItem('showPageMenu')

  if (display === null) {
    display = false
  } else {
    display = display === 'true'
  }

  $('#page-menu ul').css('display', display ? 'block' : 'none')
})

global.$ = $;
window.decode = decode;
global.toggleNav = toggleNav;
global.save = save;