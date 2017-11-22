{% extends 'app.twig' %}
{% block content %}
<div class="col jumbotron jumbotron-fluid mt-5" id="home">
	<div class="container-fluid">
		<h1 class="display-3">Hello, world!</h1>
		<p class="lead">This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
		<hr class="my-4">
		<p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
		<p class="lead">
		<a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
		</p>	
	</div>
</div>
<div class="container">
	<table class="table table-striped">
		<thead>
		<tr>
			<th>type</th>
			<th>summary</th>
			<th>price</th>			
			<th>action</th>
		</tr>
		</thead>
		<tbody>
			{% for item in items %}
			    
			
		<tr>
			<td>{{ item.type | upper }}</td>
			<td>{{ item.summary }}</td>
			<td>${{ item.price | number_format(2) }}</td>
			<td>
				<form action="{{path_for('me.cart.add')}}" method="post">
				<button type="submit" name="id" value="{{ item.id }}">Buy Now</button>
				</form>
			</td>
		</tr>
		{% endfor %}
		
		</tbody>
	</table>
</div>
<div class="col jumbotron jumbotron-fluid" id="home">
	<div class="container-fluid">
		<h1 class="display-3">Hello, world!</h1>
		<p class="lead">This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
		<hr class="my-4">
		<p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
		<p class="lead">
		<a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
		</p>	
	</div>
</div>
{% endblock %}

<?= $this->viewData(); ?>