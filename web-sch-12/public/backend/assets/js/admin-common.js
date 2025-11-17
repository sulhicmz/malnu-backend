// Set up CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Make blade components available globally
window.Laravel = window.Laravel || {};
window.Laravel.bladeComponents = {
    'action-menu': function(props) {
        return `
            <div class="relative text-right">
                <button onclick="toggleMenu(this)" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                    <i class="fas fa-ellipsis-vertical fa-lg"></i>
                </button>
                <div class="menu hidden absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg border z-10">
                    ${props.actions.map(action => {
                        if (action.type === 'link') {
                            return `
                                <a href="${action.url}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-${action.icon} mr-2 text-${action.color || 'blue'}-500"></i> ${action.label}
                                </a>
                            `;
                        } else if (action.type === 'form') {
                            return `
                                <form action="${action.url}" method="POST" onsubmit="return confirm('${action.confirm}')">
                                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                    <input type="hidden" name="_method" value="${action.method}">
                                    <button class="block w-full text-left px-4 py-2 text-sm text-${action.color || 'red'}-600 hover:bg-gray-50">
                                        <i class="fas fa-${action.icon} mr-2"></i> ${action.label}
                                    </button>
                                </form>
                            `;
                        }
                    }).join('')}
                </div>
            </div>
        `;
    }
};

// Action menu functions
function toggleMenu(button) {
    const menu = button.nextElementSibling;
    menu.classList.toggle('hidden');
    document.querySelectorAll('.menu').forEach(m => m !== menu && m.classList.add('hidden'));
}

document.addEventListener('click', e => {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.menu').forEach(m => m.classList.add('hidden'));
    }
});