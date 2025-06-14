self.addEventListener('message', (e) => {
    try {
        const nums = e.data;

        if (!Array.isArray(nums))
            throw new TypeError('El valor debe ser un array para ordenarlo');

        nums.sort((a, b) => a - b);

        self.postMessage({ status: 'ok', data: nums });
    } catch (err) {
        self.postMessage({ status: 'error', message: err.message });
    }
});