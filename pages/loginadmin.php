<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-success">
                        Admin Login
                    </h2>

                    <p class="text-muted">
                        Login untuk masuk ke dashboard admin
                    </p>

                </div>

                <form action="./process/login_process.php" method="POST">
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control rounded-3"
                            placeholder="Masukkan email"
                            required
                        >

                    </div>

                    <!-- Password -->
                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control rounded-3"
                            placeholder="Masukkan password"
                            required
                        >

                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        class="btn btn-success w-100 rounded-3 py-2">

                        Login Admin

                    </button>
                </form>
            </div>
        </div>
    </div>
</div>