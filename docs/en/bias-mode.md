# What is the bias mode?
You can set a desired line height in the configuration. But this line height is not always the resulting line height.  
The bias mode is the algorithm for calculating the deviation of the desired line height and the resulting line height of the whole gallery.  
There are two options for the bias mode:
 - avg (average)  
 Avg gets the bias by calculating the average deviation to the desired height of all lines in gallery (Mostly the preferred option).
 - max (maximal)  
 Max gets the bias by searching for the greatest deviation to the desired line height of all lines in a gallery (Better prevents too height lines).