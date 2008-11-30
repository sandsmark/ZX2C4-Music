if(!Array.indexOf)
{
	Array.prototype.indexOf = function(item)
	{
		for(var i = 0; i < this.length; i++)
		{
			if(this[i] == item)
			{
				return i;
			}
		}
		return -1;
	}
}