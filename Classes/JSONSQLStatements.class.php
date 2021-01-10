<?php

/**
 * JSONSQLStatements
 * A class to hold all sql statements that json api uses
 * @author Bradley Slater
 */
class  JSONSQLStatements{
    public static $getAuthors ="SELECT name FROM authors" ;
    public static $getAuthorsWithSessions = "SELECT a.name as authorName,s.name as sessionName ,sl.dayString as day, sl.startHour,sl.startMinute,sl.endHour,sl.endMinute, rooms.name as roomName
                            FROM authors a left join sessions s on a.authorId = s.chairId left join rooms on s.roomId = rooms.roomId left join slots sl on s.slotId = sl.slotId";
    public static $getSessions = "SELECT s.name as sessionName,a.name FROM sessions s inner join authors a on s.chairId = a.authorId";
    public static $getChairs = "SELECT s.name as SessionName, a.name as AuthorName FROM sessions s INNER JOIN  authors a  ON s.chairId = a.authorId";
    public static $getLogin = "SELECT username,password,admin FROM users WHERE email like :email";
    public static $updateSessions = "UPDATE sessions SET name = :name WHERE sessionId = :sessionId";
    public static $getNonEmptyAwards = "SELECT title,abstract,award FROM content WHERE award != ''";
    public static $getSchedule = "SELECT slots.type,slots.dayString as day,slots.startHour,slots.startMinute,slots.endHour,slots.endMinute,s.name,a.name as authorName,r.name as roomName
                        FROM slots  inner JOIN sessions s on slots.slotId = s.slotId inner join authors a on s.chairId = a.authorId inner join rooms r on s.roomId = r.roomId";
    public static $getRooms = "SELECT name FROM rooms";
    public static $getSlots = "SELECT slots.type,slots.dayString as day,slots.startHour,slots.startMinute,slots.endHour,slots.endMinute FROM slots";
}