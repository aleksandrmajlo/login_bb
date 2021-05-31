export default {
    methods: {
        showShwal(icon, text, close = true) {
            this.$swal.fire({
                icon: icon,
                text: text,
                showConfirmButton: false,
            });
            if (close) {
                setTimeout(() => {
                    this.$swal.close();
                }, 2000);
            }

        },
    }
}
