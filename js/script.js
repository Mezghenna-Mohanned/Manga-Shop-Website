let currentIndex = 0;
const images = document.querySelectorAll('.manga-item');
const totalImages = images.length;
const mangaList = document.getElementById('mangaList');

// Function to handle the sliding of images
function moveSlide(direction) {
    currentIndex += direction;

    // If the index goes out of bounds, loop back around
    if (currentIndex < 0) {
        currentIndex = totalImages - 1;
    } else if (currentIndex >= totalImages) {
        currentIndex = 0;
    }

    // Adjust the translateX property to shift the images
    mangaList.style.transform = `translateX(-${currentIndex * 100}%)`;
}
