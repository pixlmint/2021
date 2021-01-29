import '../css/style.css'

function reindex() {
  const xhr = new XMLHttpRequest()
  xhr.open('GET', '/search')
  xhr.send()
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        let response = {}
        try {
          response = JSON.parse(xhr.responseText)
        } catch (e) {
          console.log(e)
          return
        }

        alert(response.message)
      }
    }
  }
}

global.addLoadingIcon = function (element) {
  let icon = "<div class='loader-wrapper'><div class='loader'></div></div>"
  element.innerHTML += icon
}

global.removeLoadingIcon = function (element) {
  element.querySelector('.loader-wrapper').remove()
}

global.reindex = reindex
