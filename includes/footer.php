<?php
/**
 * Footer - Bagian akhir HTML
 * Menutup content-wrapper, memuat JavaScript, dan menutup body/html
 */
?>
            </div><!-- /.container-fluid -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer text-center">
        <strong>&copy; <?= date('Y') ?> <a href="<?= BASE_URL ?>">SIAKAD</a></strong> - Sistem Informasi Akademik.
    </footer>
</div><!-- /.wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // Auto-hide alert setelah 5 detik
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 5000);
    });
</script>
</body>
</html>
