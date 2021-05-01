import { decode, encode } from 'js-base64';

class App {
    constructor() {
        this.nav = []
        this.loadedPages = {}
        this.content = document.querySelector('article')
    }

    loadPage(page) {
        this.content.innerHTML = ''
        addLoadingIcon(this.content)

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
        document.querySelector('meta[name="description"]').remove()
        document.querySelector('head').innerHTML +=
            '<meta name="description" content="' + jsonPage.description + '">'

        document.querySelector('aside ul').innerHTML += "<li><a href='#" + jsonPage.id + "'>" + jsonPage.title + "</a></li>";
    }
}

const app = new App()
let startPage = location.pathname
if (location.pathname === '/') {
    startPage = '/';
}

app.loadPage(startPage)