<!DOCTYPE html>
<!-- http://bl.ocks.org/mbostock/4600693 -->
<!-- http://bl.ocks.org/mbostock/4062045 -->
<!-- http://bl.ocks.org/mbostock/1095795 -->
<html>
<head>
<title>Visualization | wave.io</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
	.node {
		stroke: #fff;
		stroke-width: 1.5px;
	}

	.node text {
	  pointer-events: none;
	  font: 10px sans-serif;
	  color: #333;
	}

	.link {
		fill: none;
		stroke: #fff;
        stroke-dasharray: 5, 5, 5, 5, 5, 5;
	}

    .list-group-item:first-child, .list-group-item:last-child {
        border-radius: 0;
    }

    body {
        background-color: #aaa;
    }
	
	/* svg { border: 1px solid #999; } */
</style>
<!--<script src="https://d3js.org/d3.v4.min.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="js/d3.legend.js"></script>
</head>
<body>
<div class="row" style="margin-right: 0; margin-left: 0">
	<div class="col-md-8">
		<svg id="nodearea" height="900" width="1000"></svg>

		<div style="display: none;">
			<button id="addNode" onClick="add_node();">Add Node</button>
			<button id="removeNode" onClick="remove_node();">Delete Node</button>
			<button id="addLink" onClick="add_link();">Add Link</button>
			<button id="removeLink" onClick="remove_link();">Delete Link</button>
		</div>
	</div>
	<div class="col-md-4" style="overflow: auto; padding-right: 0;">
		<ul class="list-group" id="event">
			<li class="list-group-item">Community Network Activated</li>
		</ul>
	</div>
</div>

<script>
var color = d3.scale.category10();
var svg = d3.select("#nodearea");
var width = svg.attr("width");
var height = svg.attr("height");

var gnodes = [];
var nodes = [];
var links = [];

var force = d3.layout.force()
    .nodes(nodes)
    .links(links)
    .charge(-400)
    .linkDistance(120)
    .size([width, height])
    .on("tick", tick);

var node = svg.selectAll(".node");
var link = svg.selectAll(".link");
      
//setInterval( refresh_source, 1000);
refresh_source();

setInterval( autoEvent, 3500);

function start() {
  node = node.data(force.nodes(), function(d) { return d.id;});
  node.enter().append("circle").attr("class", function(d) { return "node " + d.id; }).attr("r", 8).attr("fill", function(d) { return color(d.role); });
  node.append("text").attr("dx", 12).attr("dy", ".35em").text("test")
  node.exit().remove();


  link = link.data(force.links(), function(d) {
      return d.source.id + "-" + d.target.id;
  });
  link.enter().insert("line", ".node").attr("class", "link");
  link.exit().remove();

  force.start();
}

function tick() {
  node.attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("type", function(d) { return d.type })

  link.attr("x1", function(d) { return d.source.x; })
      .attr("y1", function(d) { return d.source.y; })
      .attr("x2", function(d) { return d.target.x; })
      .attr("y2", function(d) { return d.target.y; });
}

function add_node()
{
    var roles = ['Master', 'Client', 'Router'];
    var rand_item = Math.floor( getRandomNumber(1, 3) );

	var a = {id: nodes.length, role: roles[rand_item] };
	nodes.push(a);

	start();
}

function remove_node()
{
	nodes.pop();
	start();
}

function add_link()
{
	var s = Math.floor(Math.random() * nodes.length);
	var t = Math.floor(Math.random() * nodes.length);

    var node1_role = nodes[s].role;
    var node2_role = nodes[s].role;

    if( ( node1_role != 'Master' && node2_role != 'Master' ) || ( node1_role != 'Router' && node2_role != 'Router' ) )
    {
        links.push({"source": nodes[s], "target": nodes[t]});
        start();
        $('#event').prepend('<li class="list-group-item text-success">New connection established between two nodes</li>');
        $(window).scrollTop(0);
    }
}

function remove_link()
{
	var l = links.pop();
	//alert(JSON.stringify(l));
	start();
}

/*
 * Node index is the position in the data array or -1 if not found
 */
function get_node_index(id)
{
	var counter = 0;
	var index = -1;
	nodes.forEach(function(node)
	{
		if(node.id == id)
			index=counter;
		counter++;
	});
	return index;
}

function refresh_source()
{
	d3.json("nodes.php", function(error, graph) {
		if(error) throw error;

		//check for new nodes
		graph.nodes.forEach(function(newnode) {
			var found = false;
			
			nodes.forEach(function(node) {
				if(node.id == newnode.id)
					found = true;
			});
			
			if(found == false)
			{
				//alert(JSON.stringify(newnode));
				nodes.push(newnode);
			}
		});
		start();
		
		// check for nodes to remove
		var count = 0;
		nodes.forEach(function(oldnode) {
			var found = false;
			
			graph.nodes.forEach(function(node) {
				if(oldnode.id == node.id)
					found = true;
			});
			
			if(found == false)
				nodes.splice(count, 1);
			
			count++;
		});
		start();
		
		//check for new links
		graph.links.forEach(function(newLink) {
			
			var s = get_node_index(newLink.source);
			var t = get_node_index(newLink.target);
			
			if((s != -1) && (t != -1))
			{
				var found = false;
				links.forEach(function(link) {
					if((link.source.id == newLink.source) && (link.target.id == newLink.target))
						found = true;
				});
				
				if(found == false)
					links.push({"source": s, "target": t});
			}	
		});
		start();
		
		//check for links to remove
		var count = 0;
		links.forEach(function(oldLink) {
			var found = false;
			
			graph.links.forEach(function(link) {
				//alert("S: " + oldLink.source.id + " " + link.source + " T: " + oldLink.target.id + " " + link.target);
				if((oldLink.source.id == link.source) && (oldLink.target.id == link.target))
					found = true;
			});
			
			if(found == false)
				links.splice(count, 1);
			
			count++;
		});	
		start();
		
	});
}


	function autoEvent() {
		var item = Math.floor(getRandomNumber(1, 4)),
			event_obj = $('#event');

		//console.log(nodes);

		switch ( item )
		{
			case 1:
				add_link();
				break;

			case 2:
				add_node();
                add_link();
				event_obj.prepend('<li class="list-group-item text-success">New node is is appeared to the community</li>');
				break;

			case 3:
				remove_link();
				event_obj.prepend('<li class="list-group-item text-danger">Connection is dropped between two nodes</li>');
				break;

			case 4:
				remove_node();
				event_obj.prepend('<li class="list-group-item text-danger">A node is dropped from the community</li>');
				break;
		}

        $(window).scrollTop(0);
	}

	function getRandomNumber(min, max) {
		return Math.random() * (max - min) + min;
	}

</script>
</body>
</html>
