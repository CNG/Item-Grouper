I am trying to build a program that will solve such problems as:
18 people need to meet each other during group interviews over a certain period. Four rooms are available, so there will be four interview groups at once. We need to come up with an optimal (or close to it) set of combinations so everyone can meet each other in as few sessions as possible, so the sessions can be as long as possible.

First I built two classes to represent items and groups. I haven't documented them yet, but the public methods should be pretty obvious.

Then I wrote some functional programming to create a number of items and attempt to group them.

The first strategy I did, which is commented out at the bottom of the file, was roughly: Loop through the items, assigning each one to a group, favoring the group with the most members needing to be met, and then if it's equal, favoring the group with the fewest cumulative time of meeting each group member.

That code was not solving the problem at all, so I tried a new approach: Loop through the items, and have each item loop through all the other items, saving items that need to be exposed to the first item in a list. If the first item has met all of the other items, then go increase the threshold a bit. Do this for all items and add them to the self selected groups.

The last approach solves the problem, but in way too many iterations.

I think I am going to try to work on the last approach a bit more. I'll try: If the first item realizes it's met all the rest, instead of increasing the threshold for times having met the others, first move on to another item that still needs to meet someone. I am hopeful this modification will finally solve it... we'll see when I get time to try it.
