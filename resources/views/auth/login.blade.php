@extends('layouts.loginApp')

@section('content')
   <!-- Navbar -->
	<nav class="navbar navbar-expand-lg bg-primary navbar-transparent navbar-absolute" color-on-scroll="500">
		<div class="container">
		<div class="navbar-wrapper">
			  <a class="navbar-brand" href="home">Intranet Prigo</a>
		</div>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
		  <span class="sr-only">Toggle navigation</span>
		  <span class="navbar-toggler-icon icon-bar"></span>
		  <span class="navbar-toggler-icon icon-bar"></span>
		  <span class="navbar-toggler-icon icon-bar"></span>
		</button>
		</div>
	</nav>
	<!-- End Navbar -->
    <div class="wrapper wrapper-full-page">
            <div class="page-header login-page header-filter" filter-color="black" style="background-image: url('images/Maison-Kayser-pastries.jpg'); background-size: cover; background-position: top center;">
        <!--   you can change the color of the filter page using: data-color="blue | purple | green | orange | red | rose " -->

            <div class="container">
                <div class="col-md-4 col-sm-6 ml-auto mr-auto">
                    <form method="POST" action="http://127.0.0.1:8000/login">
						{{ csrf_field() }}
						<div class="card card-login">

							<div class="card-header card-header-warning text-center">
								<img src="images/logo_prigo_mini.png">
							</div>

							<div class="card-body ">
								<p class="card-description text-center">Iniciar Sesion</p>

								<span class="bmd-form-group">
									<div class="input-group">
									  <div class="input-group-prepend">
										<span class="input-group-text">
											<i class="material-icons">email</i>
										</span>
									  </div>
										<input type="email"  name="email" autofocus class="form-control" placeholder="Correo Electronico...">
									@if ($errors->has('email'))
										<span class="help-block">
											<strong>{{ $errors->first('email') }}</strong>
										</span>
									@endif
									</div>
								</span>

								<span class="bmd-form-group">
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="material-icons">lock_outline</i>
											</span>
										</div>
										<input type="password" class="form-control" name="password" required placeholder="Contraseña...">
										@if ($errors->has('password'))
										<span class="help-block">
										<strong>{{ $errors->first('password') }}</strong>
										</span>
										@endif
									</div>
								</span>
							</div>
							<div class="card-footer justify-content-center">
								<button class="btn btn-warning btn-link btn-lg" type="submit">Ingresar</button>
							</div>

						</div>
                    </form>

                </div>
            </div>
		</div>
		<footer class="footer ">
				<div class="container">
					<nav class="pull-left">
						<ul>
							<li>
								<a href="https://www.carmelaysal.mx/">
									Carmela & Sal
								</a>
							</li>
							<li>
								<a href="http://maison-kayser.com.mx/">
								   Maison Kayser
								</a>
							</li>
						</ul>
					</nav>
					<div class="copyright pull-right">
						© <script>document.write(new Date().getFullYear())</script>,Grupo Prigo.
					</div>
				</div>
			</footer>
	</div>
@endsection