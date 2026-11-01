
//NAvigation
        var navLinks = document.getElementById("navLinks");
        function showMenu(){
            if (navLinks) navLinks.style.right= "0";
            
        }
        function hideMenu(){
            if (navLinks) navLinks.style.right= "-200px";
            
        }


// Submit form
    document.getElementById("myForm")?.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent default form submission

        alert("This demo form is not connected to a service. Do not enter personal information.");
    });
    
    let slideIndex = 1;
    showSlides(slideIndex);
    
    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);
    }
    
    // Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }
    
    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        if (!slides.length) return;
        if (n > slides.length) {slideIndex = 1}    
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";  
        if (dots[slideIndex-1]) dots[slideIndex-1].className += " active";
    }

    // script.js
// window.onload = function() {
//     alert("Welcome to the Registration Page!");
// }
function showMenu() {
    const menu = document.getElementById('navLinks');
    if (menu) menu.style.right = "0";
}

function hideMenu() {
    const menu = document.getElementById('navLinks');
    if (menu) menu.style.right = "-200px";
}

const pendingCartActions = new Set();
async function requestJson(url, fields) {
    const options = {credentials: 'same-origin'};
    if (fields) {
        options.method = 'POST';
        options.body = new URLSearchParams({...fields, csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || ''});
    }
    const response = await fetch(url, options);
    let data;
    try { data = await response.json(); } catch (_) { throw new Error('Invalid server response. Reload and try again.'); }
    if (!response.ok) throw new Error(data.message || 'Request failed. Sign in or reload and try again.');
    return data;
}
async function addToCart(productId) {
    if (pendingCartActions.has(productId)) return;
    pendingCartActions.add(productId);
    try {
        const data = await requestJson('add_to_cart.php', {product_id: productId});
        if (data.status !== 'success') throw new Error(data.message || 'Unable to add item.');
        await updateCartSummary();
    } catch (error) { alert(error.message || 'Network request failed.'); }
    finally { pendingCartActions.delete(productId); }
}
async function updateCartSummary() {
    if (!document.querySelector('.wizard')) return;
    const summary = await requestJson('fetch_cart_summary.php');
    if (!Number.isFinite(summary.totalCost)) throw new Error('Invalid cart total.');
    const lines = ['Username: ' + summary.username, 'Items in Cart: ' + summary.numItems,
        'Last Listed Item: ' + summary.lastItem, 'Total Cost: $' + summary.totalCost.toFixed(2)];
    lines.forEach((text, i) => {
        const node = document.querySelector('.wizard p:nth-child(' + (i + 2) + ')');
        if (node) node.textContent = text;
    });
}

document.getElementById('searchBar')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const sections = ['fullrooms', 'livingroom', 'kitchen', 'diningroom', 'bedroom', 'office', 'bathroom'];
    const items = document.querySelectorAll('.product .description h3');
    let found = false;

    // Search for section
    sections.forEach(section => {
        const sectionName = document.querySelector(`a[href="#${section}"]`).textContent.toLowerCase();
        if (sectionName.includes(query)) {
            document.querySelector(`a[href="#${section}"]`).scrollIntoView({ behavior: 'smooth' });
            document.getElementById('searchResult').textContent = '';
            found = true;
        }
    });

    // Search for items (product names)
    items.forEach(item => {
        const itemName = item.textContent.toLowerCase();
        if (itemName.includes(query)) {
            item.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('searchResult').textContent = '';
            found = true;
        }
    });

    // If no result found
    if (!found && query.length > 0) {
        document.getElementById('searchResult').textContent = `No "${query}" found.`;
    } else if (query.length === 0) {
        document.getElementById('searchResult').textContent = '';
    }
});


async function removeFromCart(productId) {
    if (pendingCartActions.has(productId)) return;
    pendingCartActions.add(productId);
    try {
        const data = await requestJson('remove_item.php', {product_id: productId});
        if (!data.success || !Number.isInteger(data.quantity) || data.quantity < 0)
            throw new Error(data.message || 'Invalid cart response.');
        const row = document.getElementById('cart-product-' + productId);
        if (row && data.quantity === 0) row.remove();
        else if (row) {
            const label = row.querySelector('.product-quantity');
            if (label) label.textContent = 'Quantity: ' + data.quantity;
        }
        const area = document.querySelector('.cart-items');
        if (area && !area.querySelector('.cart-product') && !area.querySelector('.empty-cart')) {
            const message = document.createElement('p');
            message.className = 'empty-cart'; message.textContent = 'Your cart is empty.'; area.appendChild(message);
        }
    } catch (error) { alert(error.message || 'Network request failed.'); }
    finally { pendingCartActions.delete(productId); }
}
