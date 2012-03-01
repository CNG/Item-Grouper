I am trying to build a program that will solve such problems as:
18 people need to meet each other during group interviews over a certain period. Four rooms are available, so there will be four interview groups at once. We need to come up with an optimal (or close to it) set of combinations so everyone can meet each other in as few sessions as possible, so the sessions can be as long as possible.

First I built two classes to represent items and groups. I haven't documented them yet, but the public methods should be pretty obvious.

Then I wrote some functional programming to create a number of items and attempt to group them.

The first strategy I did, which is commented out at the bottom of the file, was roughly: Loop through the items, assigning each one to a group, favoring the group with the most members needing to be met, and then if it's equal, favoring the group with the fewest cumulative time of meeting each group member.

That code was not solving the problem at all, so I tried a new approach: Loop through the items, and have each item loop through all the other items, saving items that need to be exposed to the first item in a list. If the first item has met all of the other items, then go increase the threshold a bit. Do this for all items and add them to the self selected groups.

The second approach solves the problem, but in way too many iterations.

I think I am going to try to work on the last approach a bit more. I'll try: If the first item realizes it's met all the rest, instead of increasing the threshold for times having met the others, first move on to another item that still needs to meet someone. I am hopeful this modification will finally solve it... we'll see when I get time to try it.

Finally I came up with a reasonable way to do this, but it still solves the problem in too many iterations, though a lot better than before.

From the source code:

/**
 * METHOD C
 * This method should get very close to the solution:
 * 1. Copy Item array and shuffle on each iteration. (The effect of this might 
 *    be minimal after the next step, especially after a few iterations.)
 * 2. Sort Items by number of Items to which they have been exposed, in
 *    ascending order.
 * 3. Take the first Item and check for exposure to each other Item, 
 *    starting at the end with the least exposed Items. Each time an 
 *    unexposed Item is happened upon, add to an array of groupmates until
 *    the maximum group size has been reached. If last item is examined and
 *    the maximum group size has not been reached, increase the threshold.
 * 4. Add the Items to a Group and remove the Items from the pending array.
 * 5. Repeat until all Items are in Groups.
 * 
 * Ideally I think we would look at all possible max group size combinations
 * of the available items and favor the group with the maximum overall number
 * of potential new exposures to be made. Then take the remaining items and 
 * do the same, until none are left. This would be a greatly more resource
 * intensive operation, and may not yield much better results, especially for
 * smaller item counts.
 */

For example, you can go to this URL: http://charliecorp.com/cmb/?i=%22A%20B%20C%20D%20E%20F%20G%20H%22&g=2
This is 8 items, 2 groups at a time.
While loop took 0.001863956451416 seconds.
After 5 iterations, here is the item info:
Item A (exposures: B3,C3,D3,E1,F1,G1,H3)
Item B (exposures: A3,C3,D3,H3,E1,F1,G1)
Item C (exposures: A3,B3,D3,H3,E1,F1,G1)
Item D (exposures: A3,B3,C3,H3,E1,F1,G1)
Item E (exposures: F5,G5,H1,A1,B1,C1,D1)
Item F (exposures: E5,G5,H1,A1,B1,C1,D1)
Item G (exposures: E5,F5,H1,A1,B1,C1,D1)
Item H (exposures: E1,F1,G1,B3,C3,D3,A3)

For our 18,4 example: http://charliecorp.com/cmb/?i=%22A%20B%20C%20D%20E%20F%20G%20H%20I%20J%20K%20L%20M%20N%20O%20P%20Q%20R%22&g=4
While loop took 0.011133909225464 seconds.
After 11 iterations, here is the item info:
Item A (exposures: B4,C5,D3,E3,L2,O3,H2,I2,J1,R1,K2,N2,F1,M1,Q3,G1,P1)
Item B (exposures: A4,C4,D4,E3,L4,O4,F1,M2,Q3,G1,K2,H2,J3,P1,R2,I2,N1)
Item C (exposures: A5,B4,D4,E2,L2,O3,K3,N2,P2,F1,G2,Q3,R2,H2,I1,M1,J1)
Item D (exposures: A3,B4,C4,E3,L3,O3,H3,I2,J1,M2,Q2,F2,G2,K3,R1,N1,P1)
Item E (exposures: A3,B3,C2,D3,M2,F2,G1,R3,L3,K2,O2,P2,N2,I1,H2,J2,Q1)
Item F (exposures: G8,H2,I3,J3,M4,E2,P3,B1,Q3,C1,R1,D2,K1,A1,N3,O2,L1)
Item G (exposures: F8,H1,I3,J2,M3,E1,Q4,N3,B1,C2,R1,D2,K3,P4,O1,A1,L1)
Item H (exposures: F2,G1,I5,J7,N2,P1,A2,R3,D3,M1,O2,B2,L2,C2,E2,Q1,K2)
Item I (exposures: F3,G3,H5,J5,N4,P2,A2,R3,D2,M3,E1,C1,K1,O1,L2,B2,Q1)
Item J (exposures: F3,G2,H7,I5,N2,P4,A1,R4,D1,M2,O1,Q1,B3,L2,E2,C1,K1)
Item K (exposures: L2,M2,N2,O3,P4,Q2,R2,E2,C3,A2,B2,D3,F1,G3,I1,H2,J1)
Item L (exposures: K2,M2,N2,A2,B4,C2,D3,R2,E3,O3,P2,H2,J2,Q3,I2,F1,G1)
Item M (exposures: K2,L2,N4,E2,F4,G3,Q2,B2,D2,H1,I3,J2,P1,R3,A1,C1,O1)
Item N (exposures: K2,L2,M4,H2,I4,J2,Q1,G3,C2,P3,E2,A2,F3,O1,R2,D1,B1)
Item O (exposures: P2,Q4,R2,K3,A3,B4,C3,D3,L3,E2,H2,J1,I1,N1,G1,F2,M1)
Item P (exposures: O2,Q2,R4,K4,H1,I2,J4,F3,C2,N3,L2,E2,M1,G4,B1,A1,D1)
Item Q (exposures: O4,P2,R3,K2,G4,M2,N1,B3,F3,C3,D2,J1,L3,A3,H1,I1,E1)
Item R (exposures: O2,P4,Q3,K2,E3,L2,A1,H3,I3,J4,C2,F1,G1,M3,D1,B2,N2)
