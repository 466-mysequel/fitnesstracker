    <footer id="footer" class="footer bg-dark" style="padding: 0 15px;">
        <div class="container text-center">
            <span class="text-muted" style="font-size: small;">Fitness Tracker &copy; 2020 Team MySequel - <a href="https://github.com/466-mysequel/fitnesstracker/" alt="Fitness Tracker on Github" title="Fitness Tracker on Github">Fitness Tracker on Github</a><?php global $start; if(isset($start) && !is_null($start)) echo ' - Page generated in ' . number_format((hrtime(true) - $start) / 1e+9, 5) . ' seconds'; ?></span>
        </div>
    </footer>
</body>
</html>
