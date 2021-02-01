import Cropper from 'cropperjs'
import '../../node_modules/cropperjs/src/css/cropper.css'

global.croppers = {};

global.setCroppers = function (month, cropData = '{"cover": {}, "banner": {}}') {
    if (croppers[month] && croppers[month].cover) {
        croppers[month].cover.destroy();
    }
    if (croppers[month] && croppers[month].banner) {
        croppers[month].banner.destroy();
    }
    const cover = document.getElementById(month + '_cover');
    const banner = document.getElementById(month + '_banner');
    const data = JSON.parse(cropData);
    const coverCropper = new Cropper(cover, {
        aspectRatio: 1 / 1,
        data: data.cover,
    });

    const bannerCropper = new Cropper(banner, {
        aspectRatio: 4 / 1,
        data: data.banner,
    });

    croppers[month] = {
        original: document.getElementById(month + '_image'),
        cover: coverCropper,
        banner: bannerCropper,
    };
}

global.uploadOriginal = function (file, month) {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', '/admin/edit/home/upload-original')
    const data = new FormData();
    data.append('original', file);
    data.append('month', month);
    xhr.send(data)
    xhr.onreadystatechange = function (e) {
        if (xhr.readyState === 4) {
            const data = JSON.parse(xhr.responseText);
            console.log(data);

            document.getElementById(month + '_image').src = data.file;
            document.getElementById(month + '_cover').src = data.file;
            document.getElementById(month + '_banner').src = data.file;

            setCroppers(month);
        }
    }
}

global.storeFiles = function (event, month) {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', '/admin/edit/home')
    const data = new FormData()
    const coverFile = croppers[month].cover.getCroppedCanvas().toDataURL('image/jpeg')
    const bannerFile = croppers[month].banner.getCroppedCanvas().toDataURL('image/jpeg')
    console.log(coverFile)
    console.log(bannerFile);
    data.append('month', month)
    data.append('cover', coverFile)
    data.append('banner', bannerFile)
    data.append('cropCover', JSON.stringify(croppers[month].cover.getData(true)))
    data.append('cropBanner', JSON.stringify(croppers[month].banner.getData(true)))
    console.log(data.get('cover'));
    console.log(data.get('banner'));
    xhr.send(data);
    xhr.onreadystatechange = function (e) {
        if (xhr.readyState === 4) {
            console.log(xhr.responseText)
        }
    }
}