function fetchHomepage() {
    addLoadingIcon(document.querySelector('article'))
    fetch('/public/homepage.json')
        .then(response => response.json())
        .then(function (data) {
            data.forEach(function (month) {
                let now = new Date();
                let isFuture = false;
                if (now.getMonth() < data.indexOf(month)) {
                    isFuture = true;
                }
                console.log(month)
                document.getElementById('months-list').innerHTML += '<div onclick="location.href=\'/' + month.name + '\'" style="background-image:url(\'' + month.image + '\')" class="month' + (isFuture ? " future" : "") + '"><p>' + month.name + '</p></div>';
            });
            removeLoadingIcon(document.querySelector('article'));
        });
}

fetchHomepage();