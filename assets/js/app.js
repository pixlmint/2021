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

window.onload = function(event) {
  const title = document.querySelector('h1')
  if (window.innerWidth <= 600) {
    title.style.fontSize = window.innerWidth / (title.innerText.length - 0.5) + 'px'
  }
}


global.dataUrlToFile = function (dataUrl) {
  // convert base64 to raw binary data held in a string
  // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
  var byteString = atob(dataUrl.split(',')[1]);

  // separate out the mime component
  var mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0];

  // write the bytes of the string to an ArrayBuffer
  var ab = new ArrayBuffer(byteString.length);
  var ia = new Uint8Array(ab);
  for (var i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }

  //Old Code
  //write the ArrayBuffer to a blob, and you're done
  //var bb = new BlobBuilder();
  //bb.append(ab);
  //return bb.getBlob(mimeString);

  //New Code
  return new Blob([ab], { type: mimeString });
}


global.toggleFoldContent = function (foldId) {
  let ele = document.querySelector(foldId);
  if (ele.classList.contains('active')) {
    ele.classList.remove('active');
  } else {
    ele.classList.add('active');
  }
}

if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/ServiceWorker.js').then(function (registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function (err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
}

global.addLoadingIcon = function (element) {
  let icon = "<div class='loader-wrapper'><div class='loader'></div></div>"
  element.innerHTML += icon
}

global.removeLoadingIcon = function (element) {
  element.querySelector('.loader-wrapper').remove()
}

global.reindex = reindex
