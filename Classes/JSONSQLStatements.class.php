<?php

/**
 * JSONSQLStatements
 * A class to hold all sql statements that json api uses
 * @author Bradley Slater
 */
class  JSONSQLStatements{
    public static $getAuthors ="SELECT name FROM authors" ;
    public static $getAuthorsWithSessions = "SELECT distinct a.name as authorName,s.name as sessionName,ca.authorInst, st.name,rooms.name as roomName, sl.dayString as day, sl.startHour,sl.startMinute,sl.endHour,sl.endMinute 
FROM authors a left join sessions s on a.authorId = s.chairId left join rooms on s.roomId = rooms.roomId left join content_authors ca on a.authorId = ca.authorId 
    left join session_types st on s.typeId = st.typeId left join slots sl on s.slotId = sl.slotId group by a.name";
    public static $getSessions = "select s.sessionId,s.name as sessionName, a.name as authorName ,r.name as roomName,slots.type,slots.dayString as day,slots.startHour,slots.startMinute,slots.endHour,slots.endMinute,slots.slotId
from slots inner join sessions s on slots.slotId = s.slotId inner join authors a on a.authorId = s.chairId inner join rooms r on r.roomId = s.roomId";
    public static $getChairs = "SELECT s.name as SessionName, a.name as AuthorName FROM sessions s INNER JOIN  authors a  ON s.chairId = a.authorId";
    public static $getLogin = "SELECT username,password,admin FROM users WHERE email like :email";
    public static $updateSessions = "UPDATE sessions SET name = :name WHERE sessionId = :sessionId";
    public static $getNonEmptyAwards = "SELECT title,abstract,award FROM content WHERE award != ''";
    public static $getContent  = "select * from content inner join sessions_content on content.contentId = sessions_content.contentId";
    public static $getRooms = "SELECT name FROM rooms";

    public static $getSlots = "SELECT slots.type,slots.dayString as day,slots.startHour,slots.startMinute,slots.endHour,slots.endMinute FROM slots";
    public static $getAuthorWithSessionId = "select title,abstract,award,group_concat(a.name)as authors,authorInst,st.name as type from sessions inner join sessions_content sc on sessions.sessionId = sc.sessionId inner join content c on sc.contentId = c.contentId inner join content_authors ca on c.contentId = ca.contentId inner join authors a on a.authorId = ca.authorId
        inner join session_types st on sessions.typeId = st.typeId ";
}