const $ = require('jquery');
import { decode, encode } from 'js-base64';

function requestPage(event) {
    event.preventDefault()
    app.loadPage(event.target.getAttribute('page'))
}

class App {
    constructor() {
        this.nav = []
        this.loadedPages = {}
        this.content = document.querySelector('article')
    }

    loadPage(page) {
        this.content.innerHTML = ''
        addLoadingIcon(this.content)
        history.pushState({}, '', page)

        fetch('/nacho?p=' + page)
            .then(response => response.text())
            .then(function (data) {
                try {
                    data = JSON.parse(data)
                } catch (e) {
                    console.log(e)
                    data = { title: '500', content: encode('There was an error') }
                }
                data.forEach(function (subPage) {
                    app.printPage(subPage)
                })
                removeLoadingIcon(app.content);
            })
    }

    printPage(jsonPage) {
        this.content.innerHTML += "<h2 id='" + jsonPage.id + "'>" + jsonPage.meta.title + "</h2>" + decode(jsonPage.content)
        document.title = jsonPage.title + ' · Journal'
        document.querySelector('meta[name="description"]').remove()
        document.querySelector('head').innerHTML +=
            '<meta name="description" content="' + jsonPage.description + '">'

        document.querySelector('aside ul').innerHTML += "<li><a href='#" + jsonPage.id + "'>" + jsonPage.title + "</a></li>";
    }
}

function addLoadingIcon(element) {
    let icon = "<div class='loader-wrapper'><div class='loader'></div></div>"
    element.innerHTML += icon
}

function removeLoadingIcon(element) {
    element.querySelector('.loader-wrapper').remove()
}

const app = new App()
let startPage = location.pathname
if (location.pathname === '/') {
    startPage = '/';
}
// app.loadNav()
app.loadPage(startPage)

function getHeadingsInView() {
    let eleFound = false
    $('h1,h2,h3,h4').each(function () {
        try {
            document
                .querySelector('[href="#' + this.id + '"]')
                .classList.remove('active')
        } catch (e) {
        }
        if (this.getBoundingClientRect().y >= window.scrollY && !eleFound) {
            eleFound = true
            let link = document.querySelector('[href="#' + this.id + '"]')
            // if (link.scrollIntoViewIfNeeded !== undefined) {
            //   link.scrollIntoViewIfNeeded()
            // } else {
            //   link.scrollIntoView()
            // }
            link.classList.add('active')
        }
    })
}

function toggleMainNav() {
    const hamburg = document.getElementById('hamburg')
    const menu = document.querySelector('nav#site-nav')
    if (hamburg.checked) {
        menu.classList.remove('collapsed')
    } else {
        menu.classList.add('collapsed')
    }
}
