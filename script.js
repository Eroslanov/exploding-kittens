document.addEventListener('DOMContentLoaded', function () {
    const drawButton = document.querySelector('button[name="action"][value="вытянуть"]');
    drawButton.addEventListener('click', function (event) {
        event.preventDefault();
        fetch('game.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=вытянуть'
        })
        .then(response => response.text())
        .then(html => {
            document.open();
            document.write(html);
            document.close();
        })
        .catch(error => console.error('Error:', error));
    });
});