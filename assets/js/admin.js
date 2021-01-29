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

function uploadImage() {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '/admin/image/upload');
  let data = new FormData();
  data.append('month', document.querySelector('[name="month"]').value);
  data.append('day', document.querySelector('[name="day"]').value);
  let files = document.querySelector('[name="image"]').files;
  for (let i = 0; i < files.length; i++) {
    let file = files.item(i);
    data.append(file.name, file);
  }
  xhr.send(data);
  xhr.onreadystatechange = function (e) {
    if (xhr.readyState === 4) {
      loadImages();
    }
  }
}

function loadImages() {
  const imageList = document.getElementById('images-list');
  const xhr = new XMLHttpRequest();
  let params = new URLSearchParams('');
  params.append('month', document.querySelector('[name="month"]').value);
  params.append('day', document.querySelector('[name="day"]').value);
  xhr.open('GET', '/admin/image/load?' + params.toString());
  xhr.send();
  xhr.onreadystatechange = function (e) {
    if (xhr.readyState === 4 && xhr.status < 300) {
      imageList.innerHTML = '';
      let response = JSON.parse(xhr.responseText);
      console.log(response);
      response.images.forEach(function (imageUrl) {
        imageList.innerHTML += "<li><a class='create-md-image' href='" + imageUrl + "' target='_blank'>" + imageUrl + "</a></li>";
      });

      $('.create-md-image').on('contextmenu', function (e) {
        const copyMe = document.getElementById('copy-me');
        copyMe.value = "![image](" + e.target + ")";
        copyMe.select();
        copyMe.setSelectionRange(0, 999999);
        document.execCommand('copy');
      })
    }
  }
}

$(function () {
  let display = localStorage.getItem('showPageMenu')

  if (display === null) {
    display = false
  } else {
    display = display === 'true'
  }

  $('#page-menu ul').css('display', display ? 'block' : 'none')
	loadImages();
})

global.$ = $;
window.decode = decode;
global.toggleNav = toggleNav;
global.save = save;