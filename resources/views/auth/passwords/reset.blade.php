@extends('layouts.loginApp')

@section('content')
   <!-- Navbar -->
	<nav class="navbar navbar-expand-lg bg-primary navbar-transparent navbar-absolute" color-on-scroll="500">
		<div class="container">
		<div class="navbar-wrapper">
			  <a class="navbar-brand" href="#">Intranet Prigo</a>
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
                    <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
						{{ csrf_field() }}
						<div class="card card-login">

							<div class="card-header card-header-warning text-center">
								<img src="images/logo_prigo_mini.png">
							</div>

							<div class="card-body ">
								<p class="card-description text-center">Recuperar contraseña</p>

								<span class="bmd-form-group">
									<div class="input-group">
									  <div class="input-group-prepend">
										<span class="input-group-text">
											<i class="material-icons">email</i>
										</span>
									  </div>
										<input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>
										@if ($errors->has('email'))
											<span class="help-block">
												<strong>{{ $errors->first('email') }}</strong>
											</span>
										@endif
									</div>
								</span>

								<span class="bmd-form-group">
									<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
										<label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>
										<div class="col-md-6">
											<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

											@if ($errors->has('password_confirmation'))
												<span class="help-block">
													<strong>{{ $errors->first('password_confirmation') }}</strong>
												</span>
											@endif
										</div>
									</div>

									<div class="form-group">
										<div class="col-md-6 col-md-offset-4">
											<button type="submit" class="btn btn-primary">
												Cambiar Contraseña
											</button>
										</div>
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
	</div>
@endsection